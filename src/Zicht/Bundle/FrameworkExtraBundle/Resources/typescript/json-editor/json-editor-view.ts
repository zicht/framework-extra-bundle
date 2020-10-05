import {JSONEditor} from '@json-editor/json-editor/src/core';
import {Xhr} from '@zicht/js.component/src/xhr/xhr';
import {getOptionalAttribute} from '@zicht/js.util/src/attribute/get-optional-attribute';
import {getRequiredAttribute} from '@zicht/js.util/src/attribute/get-required-attribute';
import {getRequiredBooleanAttribute} from '@zicht/js.util/src/attribute/get-required-boolean-attribute';
import {getRequiredJsonAttribute} from '@zicht/js.util/src/attribute/get-required-json-attribute';
import {findAndCreate} from '@zicht/js.util/src/find/find-and-create';
import {View} from '@zicht/js.util/src/view/view';
import * as alertify from 'alertifyjs';

/**
 * Create a json editor based on a given schema
 *
 * Supported data attributes:
 * - data-json-editor-popup: boolean to indicate the form must be shown in a popup window, defaults to false
 * - data-json-editor-options: a json object that is passed to the json editor, defaults to {}
 * - data-json-editor-schema: a string containing the schema
 * - data-json-editor-schema-url: a string containing the schema url
 *
 * Either the data-json-editor-schema or the data-json-editor-schema-url is required.
 */
export class JsonEditorView extends View<HTMLInputElement> {
    public static defaultDebug:boolean = false;
    public static defaultPopup:boolean = true;
    public static defaultOptions:any = {};

    public static findAndCreate(container:HTMLElement = window.document.body):JsonEditorView[] {
        return findAndCreate(container, 'input.js-json-editor', (element:HTMLElement):JsonEditorView | undefined => element instanceof HTMLInputElement ? new JsonEditorView(element) : undefined);
    }

    private _editor:any;
    private readonly _debug:boolean;
    private readonly _popup:boolean;
    private readonly _options:any;

    private constructor(element:HTMLInputElement) {
        super(element);
        this._debug = getRequiredBooleanAttribute(this._element, 'data-json-editor-debug', JsonEditorView.defaultDebug);
        this._popup = getRequiredBooleanAttribute(this._element, 'data-json-editor-popup', JsonEditorView.defaultPopup);
        this._options = {
            ...JsonEditorView.defaultOptions,
            ...getRequiredJsonAttribute(this._element, 'data-json-editor-options', (_data:any):_data is any => true, {}),
        };
        this._createEditorWrapperElement(element);
    }

    private async _initializeEditor(element:HTMLElement):Promise<void> {
        // Get the schema
        const schema:any = await this._getSchema();
        // Initialize the editor
        this._editor = new JSONEditor(element, {...this._options, schema});
        // Disable the editor when the _element is disabled
        if (this._element instanceof HTMLInputElement && this._element.disabled) {
            this._editor.disable();
        }
        // Populate the editor with the value from this._element
        this._editor.on('ready', ():void => {
            let value:any = {};
            try {
                value = JSON.parse(this._element.value);
            } catch (error) {
                window.console.warn('Unable to parse json value in element', error);
            }
            if (this._debug) {
                window.console.debug('Initial value', value);
            }
            this._editor.setValue(value);
        });
        // Store the resulting JSON in the input element
        this._editor.on('change', ():void => {
            const value:any = this._editor.getValue();
            const strValue:string = JSON.stringify(value);
            if (this._element.value !== strValue) {
                if (this._debug) {
                    window.console.debug('Update value', value);
                }
                this._element.value = strValue;
            }
        });
    }

    private async _getSchema():Promise<any> {
        // Obtain the schema from an attribute
        const schema:string | undefined = getOptionalAttribute(this._element, 'data-json-editor-schema');
        if (schema) {
            return JSON.parse(schema);
        }

        // Obtain the schema by downloading an attribute
        const schemaUrl:string | undefined = getRequiredAttribute(this._element, 'data-json-editor-schema-url');
        if (schemaUrl) {
            return Xhr.json(schemaUrl, {cache: 'memory'});
        }

        throw new Error('Either the schema or a schema url must be provided using data-json-editor-schema="<SCHEMA>" or data-json-editor-schema-url="<SCHEMA-URL>"');
    }

    private async _createPopup():Promise<void> {
        const dialog:any = alertify.dialog('alert');
        dialog.set({
            frameless: true,
            transition: 'fade',
        });
        dialog.hooks.onclose = ():void => {
            if (this._editor) {
                this._editor.destroy();
            }
        };
        this._initializeEditor(dialog.elements.content);
        dialog.show();
    }

    private _createEditorWrapperElement(element:HTMLElement):void {
        if (!element.parentElement) {
            throw new Error('Unable to inject JsonEditor, given element does not have a parent element');
        }

        if (element instanceof HTMLInputElement && element.disabled) {
            window.console.warn('The input element is disabled');
        }

        // Replace element with wrapper element
        const wrapper:HTMLDivElement = window.document.createElement('div');
        element.parentElement.replaceChild(wrapper, element);
        wrapper.appendChild(element);

        if (this._popup) {
            // Add button that will popup the editor
            const button:HTMLButtonElement = window.document.createElement('button');
            button.innerText = 'Edit';
            wrapper.appendChild(button);

            button.addEventListener('click', (event:Event):void => {
                event.preventDefault();
                this._createPopup();
            });
        } else {
            // Add the editor inline
            const editor:HTMLDivElement = window.document.createElement('div');
            wrapper.appendChild(editor);
            this._initializeEditor(editor);
        }
    }
}
