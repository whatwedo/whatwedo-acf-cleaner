/*
	VueJS for Management Page
 */

import Actions from './components/Actions.js';

const { createApp } = Vue;

const app = createApp({
    el: '#wwdac-app',
    /*
    delimiters: ['[[', ']]'],
    render() {
        return h(ManagementApp)
    },
    */
    components: {
        'actions': Actions,
    },
    template: `
        <div class="container mx-auto p-4">
            <h2 class="text-2xl mb-2">{{ title }}</h2>
            
            <!--
            <ul>
                <li :data-type="key" v-for="(postType, key) in postTypes">{{ postType }}</li>
            </ul>
            -->
                        
            <actions></actions>
        </div>
    `,
    data() {
        return {
            postTypes: wwdacData.postTypes,
            title: 'ACF Cleaner by whatwedo',
        }
    },
    created() {
        //this.postTypes = wwdacData.postTypes;
    },
});

// Only load vue when modules and fetch API are supported in the browser
if('noModule' in HTMLScriptElement.prototype && window.fetch) {
    app.mount('#wwdac-app');
} else {
    document.getElementById('wwdac-app').innerHTML = 'Wir gehen davon aus dass Entwickler die dieses Backend verwenden einen aktuellen Browser haben und nuten somit neuste Technologien.';
}
