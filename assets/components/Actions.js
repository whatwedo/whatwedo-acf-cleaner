import Progress from './Progress.js';

export default {
    components: {
        'progress-item': Progress,
    },
    template: `
        <div>
            <progress-item v-if="inProgressAction" :total="progress.totalPage" :current="progress.currentPage"></progress-item>
            <p class="mb-2">{{ progress.discoveredFields }} discovered fields within {{ progress.progressedPosts }} posts</p>

            <button class="hover:bg-blue-800 bg-blue-600 text-white rounded-md p-2 mr-2" :style="disabledStyle" :disabled="isDisabled" @click="triggerBatchDiscovery">Batch Discovery</button>
            <button class="hover:bg-red-800 bg-red-600 text-white rounded-md p-2 mr-2" :style="disabledStyle" :disabled="isDisabled" @click="triggerBatchCleanup">Batch Cleanup (DANGER)</button>
            <button class="text-black border border-black rounded-md p-2 mr-2" v-if="inProgress" @click="triggerCancel">Cancel</button>
            <style>
                button:disabled {
                  opacity: .5;
                }
            </style>
        </div>
    `,
    data() {
        return {
            batchPaged: 0,
            inProgressAction: false,
            isFinished: false,
            progress: {
                progressedPosts: 0,
                discoveredFields: 0,
                totalPage: 0,
                currentPage: 0,
            }
        }
    },
    watch: {
        batchPaged: function() {
            if(this.inProgress) {
                const postType = Object.assign({}, this.$root.shared.selectedPostTypes);
                const params = {
                    'action': this.inProgressAction,
                    'nonce': wwdacData.nonce,
                    'postType': Object.values(postType).join(','),
                    'paged': this.batchPaged,
                };
                let request = this.callBatchApi(new URLSearchParams(params).toString());
                request.then(data => {
                    if(this.inProgress) {
                        this.progress.totalPage = data.totalPage;
                        this.progress.currentPage = data.currentPage;
                        this.progress.progressedPosts += data.postIds.length;
                        data.data.forEach((value) => {
                            this.progress.discoveredFields += value.amount;
                        });

                        if(data.nextPage) {
                            this.batchPaged = data.nextPage;
                        } else {
                            this.inProgressAction = false;
                            this.isFinished = true;
                        }
                    }
                }).catch((error) => {
                    console.warn('Something went wrong.', error);
                });
            }
        },
        isFinished: function() {
            if(this.isFinished) {
                this.batchPaged = 0;
                this.inProgressAction = false;
            } else {
                this.resetValues();
            }
        },
    },
    computed: {
        inProgress: function() {
            this.$root.shared.inProgress = !!this.inProgressAction;
            return !!this.inProgressAction;
        },
        hasPostTypes: function() {
            return this.$root.shared.selectedPostTypes.length;
        },
        isDisabled: function() {
            return this.inProgress || !this.hasPostTypes;
        },
        disabledStyle: function() {
            /* Tailwind does not support disabled styling from precompiled build (CDN) */
            if(this.isDisabled) {
                return {
                    opacity: '0.5',
                }
            }
            return false;
        },
    },
    methods: {
        triggerCleanup() {
            this.inProgressAction = 'singleDiscovery';
            this.isFinished = false;
            console.log('Clean clicked');
        },
        triggerBatchDiscovery() {
            this.batchPaged = 1;
            this.inProgressAction = 'batchDiscovery';
            this.isFinished = false;
            console.log('Batch clicked');
        },
        triggerBatchCleanup() {
            if(confirm('Are you sure to modify the database? This can NOT be undone')) {
                console.log('Batch Cleanup clicked and confirmed');
                this.inProgressAction = 'batchCleanup';
                this.batchPaged = 1;
                this.isFinished = false;
            }
        },
        triggerCancel() {
            console.log('canceled');
            this.isFinished = true;

            // Reset values
            this.resetValues();
        },
        callApi(params) {
            fetch(wwdacData.ajaxurl, {
                method: 'POST',
                body: params,
                headers: {
                    'Content-type': 'application/x-www-form-urlencoded'
                }
            }).then((response) => {
                if (response.ok) {
                    return response.json();
                }
                return Promise.reject(response);
            }).then((data) => {
                console.log(data);
            }).catch((error) => {
                console.warn('Something went wrong.', error);
            });
        },
        callBatchApi(params) {
            return fetch(wwdacData.ajaxurl, {
                method: 'POST',
                body: params,
                headers: {
                    'Content-type': 'application/x-www-form-urlencoded'
                }
            }).then((response) => {
                if (response.ok) {
                    return response.json();
                }
                return Promise.reject(response);
            });
        },
        resetValues() {
            this.progress = {
                progressedPosts: 0,
                discoveredFields: 0,
                totalPage: 0,
                currentPage: 0,
            }
        }
    }
};
