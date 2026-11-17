import { Response } from 'node-fetch';
import { describe, expect, it } from 'vitest';
import type { BackendAction, BackendInterface } from '../../../src/Backend/Backend';
import BackendRequest from '../../../src/Backend/BackendRequest';
import Component from '../../../src/Component';
import { noopElementDriver } from '../../tools';

const makeComponent = (backend: BackendInterface): Component => {
    const component = new Component(
        document.createElement('div'),
        'test-component',
        { firstName: '' },
        [],
        null,
        backend,
        new noopElementDriver()
    );
    component.connect();

    return component;
};

const settle = (): Promise<void> => new Promise((resolve) => setTimeout(resolve, 50));

describe('Component after a failed request', () => {
    it('sends a new request after the previous one was rejected', async () => {
        let requests = 0;
        const component = makeComponent({
            makeRequest(_props: any, _actions: BackendAction[]): BackendRequest {
                requests++;

                // What fetch() does on a network failure, a CORS failure or an abort.
                return new BackendRequest(Promise.reject(new TypeError('Failed to fetch')), [], []);
            },
        });

        component.render().catch(() => {});
        await settle();
        expect(requests).toBe(1);

        component.render().catch(() => {});
        await settle();

        // Without clearing `backendRequest`, `tryStartingRequest()` only sets `isRequestPending`
        // and the component never talks to the server again.
        expect(requests).toBe(2);
    });

    it('still sends a new request after a successful one', async () => {
        let requests = 0;
        const component = makeComponent({
            makeRequest(_props: any, _actions: BackendAction[]): BackendRequest {
                requests++;

                return new BackendRequest(
                    // @ts-expect-error Response doesn't quite match the underlying interface
                    Promise.resolve(new Response('<div data-live-props-value="{}"></div>')),
                    [],
                    []
                );
            },
        });

        component.render().catch(() => {});
        await settle();
        expect(requests).toBe(1);

        component.render().catch(() => {});
        await settle();
        expect(requests).toBe(2);
    });
});
