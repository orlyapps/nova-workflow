<template>
    <div class="item flex items-center">
        <Card class="card w-full px-4 py-4">
            <textarea
                id="notes"
                v-model="comment"
                dusk="notes"
                rows="5"
                class="block w-full form-control form-input form-input-bordered py-3 h-auto mb-3"
            ></textarea>

            <DefaultButton
                :disabled="comment.length <= 10"
                @click.prevent.stop="onSend"
                dusk="update-button">{{ __('Submit comment') }}</DefaultButton>

        </Card>
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
