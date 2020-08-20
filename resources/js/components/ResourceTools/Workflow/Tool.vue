<template>
    <div>
        <h4 class="text-90 font-normal text-2xl mb-3">{{ __("Activities") }}</h4>
        <div class="timeline w-full">
            <workflow-write-comment @submit="onWriteComment"></workflow-write-comment>
            <workflow-status v-for="activity in activities" :key="activity.id" :activity="activity"></workflow-status>
        </div>
    </div>
</template>

<script>
export default {
    props: ["resourceName", "resourceId", "field"],
    data: () => {
        return {
            activities: [],
        };
    },
    async mounted() {
        this.fetch();
        Nova.$on("workflow-updated", () => {
            this.fetch();
        });
    },
    beforeDestroy() {
        Nova.$off("workflow-updated");
    },
    methods: {
        async onWriteComment(comment) {
            const activity = (
                await Nova.request().post(
                    `/nova-vendor/nova-workflow/logs?resourceName=${this.resourceName}&resourceId=${this.resourceId}`,
                    { comment }
                )
            ).data;
            this.$toasted.show("Kommentar erfolgreich gespeichert", { type: "success" });
            this.activities.unshift(activity);
        },
        async fetch() {
            this.activities = (
                await Nova.request().get(`/nova-vendor/nova-workflow/logs?resourceName=${this.resourceName}&resourceId=${this.resourceId}`)
            ).data.data;
        },
    },
};
</script>
<style lang="scss" >
.timeline {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    &:before {
        content: "";
        position: absolute;
        top: 0;
        left: 18px;
        height: 100%;
        width: 4px;
        background: #d7e4ed;
        left: 50%;
        margin-left: -2px;
    }
    .item {
        width: 48%;
    }
    .item:nth-child(odd) {
        align-self: flex-start;
        svg {
            margin-left: 0.75rem;
        }
    }

    .item:nth-child(even) {
        align-self: flex-end;
        flex-direction: row-reverse;
        svg {
            margin-right: 0.75rem;
        }
    }
}
</style>
