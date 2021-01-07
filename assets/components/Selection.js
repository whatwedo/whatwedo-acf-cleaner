export default {
    template: `
        <div>
            <p class="mb-2">Select the post type you want to clean:</p>
            
            <button class="text-black border border-black py-1 px-2" v-if="!isSelectedAll" @click="toggleSelectAll">Select all</button>
            <button class="text-black border border-black py-1 px-2" v-if="isSelectedAll" @click="toggleSelectAll">Unselect all</button>
            
            <div class="flex flex-col mb-4">              
                <label class="inline-flex items-center mt-3" v-for="(postType, key) in postTypes">
                    <input type="checkbox" :value="key" v-model="checkedPostTypes" class="form-checkbox h-5 w-5 text-blue-500" />
                    <span class="ml-2 text-gray-700">{{ postType }}</span>
                </label>            
            </div>
        </div>
    `,
    data() {
        return {
            checkedPostTypes: ['post', 'page'],
            postTypes: wwdacData.postTypes,
            isSelectedAll: false,
        }
    },
    watch: {
        checkedPostTypes: function() {
            this.$root.shared.selectedPostTypes = this.checkedPostTypes;

            this.isSelectedAll = this.checkSelecteAmount();
        }
    },
    methods: {
        checkSelecteAmount: function() {
            return Object.keys(this.postTypes).length === this.checkedPostTypes.length;
        },
        toggleSelectAll: function() {
            if(this.checkSelecteAmount()) {
                this.checkedPostTypes = [];
            } else {
                this.checkedPostTypes = Object.keys(this.postTypes);
            }

        }
    },
    mounted() {
        this.$root.shared.selectedPostTypes = this.checkedPostTypes;
    }
};
