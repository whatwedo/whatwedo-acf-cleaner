/*
	VueJS for Management Page
 */

import Actions from './components/Actions.js';
import Selection from './components/Selection.js';

const { createApp } = Vue;

const shared = {
    selectedPostTypes: [],
    /*
    mySharedMethod(){
        //do shared stuff
    }
    */
};
const app = createApp({
    el: '#wwdac-app',
    components: {
        'actions': Actions,
        'selection': Selection,
    },
    template: `
        <div class="container mx-auto p-4">
            <h2 class="text-2xl mb-2">{{ title }}</h2>
            
            <p>Select the post type you want to clean:</p>
            <selection></selection>            
            <actions></actions>
        </div>
    `,
    data() {
        return {
            title: 'ACF Cleaner by whatwedo',
            shared
        }
    },
});

// Only load vue when modules and fetch API are supported in the browser
if('noModule' in HTMLScriptElement.prototype && window.fetch) {
    app.mount('#wwdac-app');
} else {
    document.getElementById('wwdac-app').innerHTML = 'Wir gehen davon aus, dass Entwickler die dieses Backend verwenden, einen aktuellen Browser haben und nutzen somit neuste Technologien.';
}
