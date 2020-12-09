export default {
    template: `
        <div class="flex flex-col mb-4">
            <!--
            <label class="inline-flex items-center mt-3">
                <input value="all" @click="selectAll" type="checkbox" class="form-checkbox h-5 w-5 text-blue-500">
                <span class="ml-2 text-gray-700">Select all</span>
            </label>
            -->
            
            <label class="inline-flex items-center mt-3" v-for="(postType, key) in postTypes">
                <input type="checkbox" :value="key" v-model="checkedPostTypes" class="form-checkbox h-5 w-5 text-blue-500" />
                <span class="ml-2 text-gray-700">{{ postType }}</span>
            </label>            
        </div>
    `,
    data() {
        return {
            checkedPostTypes: ['post', 'page'],
            postTypes: wwdacData.postTypes,
        }
    },
    watch: {
        checkedPostTypes: function() {
            this.$root.shared.selectedPostTypes = this.checkedPostTypes;
        }
    },
    methods: {
    },
    mounted() {
        this.$root.shared.selectedPostTypes = this.checkedPostTypes;
    }
};
