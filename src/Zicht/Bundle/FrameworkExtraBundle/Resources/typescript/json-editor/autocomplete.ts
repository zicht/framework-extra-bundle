import {JSONEditor} from '@json-editor/json-editor/src/core';
import {Xhr} from '@zicht/js.component/src/xhr/xhr';

/**
 * https://autocomplete.trevoreyre.com/#/javascript-component?id=renderresult
 */
interface PropsInterface {
    [key:string]:any;

    toString:{ ():string };
}

/**
 * One selected or selectable result
 */
interface ResultItemInterface {
    image?:string;
    label?:string;
    type:'result' | 'info';
    value:string;
}

/**
 * Returns true when data is a ResultItemInterface
 */
const resultItemConvertor:{ (data:any):ResultItemInterface } = (data:any):ResultItemInterface => {
    // Handle case where only the value is provided
    if (typeof data === 'string') {
        return {type: 'result', value: data};
    }

    // Handle case where `value` and `label` are required and `image` is optional
    if (typeof data === 'object' && typeof data.value === 'string' && (!data.image || typeof data.image === 'string') && typeof data.label === 'string') {
        return {image: typeof data.image === 'string' ? data.image : undefined, label: data.label, type: 'result', value: data.value};
    }

    // Handle case where `info` is required
    if (typeof data === 'object' && typeof data.info === 'string') {
        return {type: 'info', value: data.info};
    }

    console.error(data);
    throw new Error('Unable to convert data to auto complete item');
};

/**
 * Tries to convert data into a ResultItemInterface[]
 */
const jsonFeedResultConvertor:{ (data:any):ResultItemInterface[] } = (data:any):ResultItemInterface[] => {
    // Handle feeds that return the list directly
    if (Array.isArray(data)) {
        return data.map(resultItemConvertor);
    }

    // Handle feeds that return the list in a result property
    if (typeof data === 'object' && Array.isArray(data.result)) {
        return data.result.map(resultItemConvertor);
    }

    console.error(data);
    throw new Error('Unable to convert data to auto complete items');
};

export const setupAutocomplete:{ ():void } = ():void => {
    JSONEditor.defaults.callbacks = {
        autocomplete: {
            // Render the result (i.e. the visible representation of the result)
            json_feed_render: (_editor:any, result:ResultItemInterface, props:PropsInterface):string => {
                switch (result.type) {
                    case 'info':
                        return [
                            '<li style="padding:15px; background-color:lightgray;">',
                            `${result.value}`,
                            '</li>',
                        ].join('');

                    case 'result':
                        return [
                            `<li ${props.toString()}>`,
                            result.image ? `<img src="${result.image}" style="float:left; width:60px; height:40px; display:block; object-fit:contain; margin-right:5px;">` : '',
                            `<div>${result.label ? result.label : result.value}</div>`,
                            result.label ? `<div><small>${result.value}</small></div>` : '',
                            `</li>`,
                        ].join('');
                }
            },

            // Get the result value (i.e. what is stored as json data)
            json_feed_result: (_editor:any, result:ResultItemInterface):string => {
                return result.value;
            },

            // Autocompletion using a JSON feed
            json_feed_search: async (editor:any, input:string):Promise<ResultItemInterface[]> => {
                if (typeof editor.options.minimumInputLength === 'number' && input.length < editor.options.minimumInputLength) {
                    return [];
                }

                if (!editor.options.url) {
                    throw new Error('"url" option is not specified in autocomplete configuration');
                }

                return Xhr.json(`${editor.options.url}${encodeURIComponent(input)}`, {convertor: jsonFeedResultConvertor});
            },
        },
    };
};
