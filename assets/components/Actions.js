import Progress from './Progress.js';

export default {
    components: {
        'progress-item': Progress,
    },
    template: `
        <div>
            <progress-item v-if="inProgressAction" :total="progress.totalPage" :current="progress.currentPage"></progress-item>
            <p class="mb-2">{{ progress.discoveredFields }} discovered fields within {{ progress.progressedPosts }} posts</p>
        
            <!--
            <button class="bg-blue-600 text-white rounded-md p-2 mr-2" @click="triggerDiscovery">Discover metadata</button>
            <button class="bg-blue-600 text-white rounded-md p-2 mr-2" @click="triggerCleanup">Clean metadata</button>
            -->
            <button class="bg-blue-600 text-white rounded-md p-2 mr-2" :disabled="inProgressAction" @click="triggerBatchDiscovery">Batch Discovery</button>
            <button class="bg-red-600 text-white rounded-md p-2 mr-2" :disabled="inProgressAction" @click="triggerBatchCleanup">Batch Cleanup (DANGER)</button>
        </div>
    `,
    data() {
        return {
            batchPaged: 0,
            inProgressAction: false,
            inProgress: false,
            inFinished: false,
            isDangerAction: false,
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
            this.callBatchApi('action=' + this.inProgressAction + '&nonce=' + wwdacData.nonce + '&postType=[post]&paged=' + this.batchPaged) // TODO
                .then(data => {
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
                }).catch((error) => {
                    console.warn('Something went wrong.', error);
                });
        }
    },
    computed: {
    },
    methods: {
        triggerDiscovery() {
            console.log('Discover clicked');

            fetch(wwdacData.ajaxurl, {
                method: 'POST',
                body: 'action=' + this.inProgressAction + '&nonce=' + wwdacData.nonce + '&postId=9522', // TODO
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
        triggerCleanup() {
            this.inProgressAction = 'singleDiscovery';
            console.log('Clean clicked');
        },
        triggerBatchDiscovery() {
            this.batchPaged = 1;
            this.inProgressAction = 'batchDiscovery';
            console.log('Batch clicked');
        },
        triggerBatchCleanup() {
            if(confirm('Are you sure to modify the database? This can NOT be undone')) {
                console.log('Batch Cleanup clicked and confirmed');
                this.inProgressAction = 'batchCleanup';
                this.batchPaged = 1;
            }
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
        }
    }
};
