export default {
    template: `
        <div class="w-full">
            <div class="shadow w-full bg-grey-light mt-2">
                <div class="bg-blue-500 text-xs leading-none py-3 mb-2 text-center text-white" :style="'width: ' + progressPercent + '%'">{{ progressPercent }}%</div>
            </div>
        </div>`,
    data() {
        return {

        }
    },
    props: {
        total: Number,
        current: Number
    },
    computed: {
        progressPercent: function () {
            if(!this.current) {
                return 0;
            }
            let progress = this.current / this.total * 100;
            return progress.toFixed();
        }
    }
};
