<template>
    <div class="item flex items-center">
        <div class="card w-full px-4 py-4">
            <textarea
                id="notes"
                v-model="comment"
                dusk="notes"
                rows="5"
                class="w-full form-control form-input form-input-bordered py-3 h-auto"
            ></textarea>
            <button
                :disabled="comment.length <= 10"
                @click.prevent.stop="onSend"
                class="btn btn-default btn-primary inline-flex items-center relative"
                dusk="update-button"
            >
                <span class>{{ __('Submit comment') }}</span>
            </button>
        </div>
    </div>
</template>

<script>
export default {
    data: () => {
        return {
            comment: "",
        };
    },
    methods: {
        onSend(event) {
            this.$emit("submit", this.comment);
            this.comment = "";
        },
    },
};
</script>
