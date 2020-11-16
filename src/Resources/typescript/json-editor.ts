import {onReady} from '@zicht/js.util/src/document/on-ready';
import {setupAutocomplete} from './json-editor/autocomplete';
import {JsonEditorView} from './json-editor/json-editor-view';

onReady(():void => {
    setupAutocomplete();
    JsonEditorView.findAndCreate();
});
