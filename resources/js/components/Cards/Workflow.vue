<template>
    <Card class="px-4 py-4 space-y-4">
        <div class="flex mb-3 relative" v-if="state.dueIn">
            <span
                class="whitespace-no-wrap px-2 py-1 rounded-full uppercase text-xs font-bold absolute pin-t pin-r"
                :class="{ 'bg-orange-light text-orange-dark': state.duePast === false, 'bg-red-light text-red-dark': state.duePast === true }"
            >
                {{ __("Due") }} {{ state.dueIn }}
            </span>
        </div>
        <div class="flex items-start">
            <div class="rounded-full mr-3 mt-1" style="height: 20px; width: 23px" :class="'bg-' + state.color"></div>
            <div class="flex justify-between w-full">
                <div>
                    <h2 class="text-xl font-bold">{{ state.title }}</h2>
                    <p class="text-sm text-gray-500">
                        {{ state.description }}
                    </p>
                </div>
                <button
                    v-if="state && state.can.changeDue"
                    @click.prevent="changeDue"
                    class="btn btn-clear btn-icon btn-white"
                    data-testid="edit-resource"
                    dusk="edit-resource-button"
                    title="Bearbeiten"
                >
                    <svg
                        aria-hidden="true"
                        focusable="false"
                        data-prefix="fal"
                        data-icon="calendar-edit"
                        class="text-80 h-6 w-6"
                        role="img"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 448 512"
                    >
                        <path
                            fill="currentColor"
                            d="M400 64h-48V12c0-6.6-5.4-12-12-12h-8c-6.6 0-12 5.4-12 12v52H128V12c0-6.6-5.4-12-12-12h-8c-6.6 0-12 5.4-12 12v52H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM48 96h352c8.8 0 16 7.2 16 16v48H32v-48c0-8.8 7.2-16 16-16zm352 384H48c-8.8 0-16-7.2-16-16V192h384v272c0 8.8-7.2 16-16 16zM255.7 269.7l34.6 34.6c2.1 2.1 2.1 5.4 0 7.4L159.1 442.9l-35.1 5c-6.9 1-12.9-4.9-11.9-11.9l5-35.1 131.2-131.2c2-2 5.4-2 7.4 0zm75.2 1.4l-19.2 19.2c-2.1 2.1-5.4 2.1-7.4 0l-34.6-34.6c-2.1-2.1-2.1-5.4 0-7.4l19.2-19.2c6.8-6.8 17.9-6.8 24.7 0l17.3 17.3c6.8 6.8 6.8 17.9 0 24.7z"
                        />
                    </svg>
                </button>
            </div>
        </div>
        <div v-if="state && state.responsibleUsers.length !== 0">
            <h3 class="uppercase tracking-wide font-bold">{{ __("Responsibility") }}</h3>
            <h5 class="font-light">
                <span v-for="user in state.responsibleUsers" :key="user.id">
                    <router-link
                        :to="{
                            name: 'detail',
                            params: {
                                resourceName: user.resourceName,
                                resourceId: user.id,
                            },
                        }"
                    >
                        {{ user.name }}
                    </router-link>
                    ,&nbsp;
                </span>
            </h5>
        </div>

        <div v-if="state.transitions && state.transitions.length" class="space-y-2">
            <div v-for="transition in state.transitions" :key="transition.name">
                <DefaultButton class="w-1/2" v-if="transition.userInteraction" @click.stop.prevent="apply(transition)">{{ transition.title }}</DefaultButton>
            </div>
        </div>
        <div class="action-selector hidden">
            <DetailActionDropdown ref="actionSelector" :resource="resource" :resource-name="resourceName" :actions="actions" :endpoint="actionsEndpoint" :query-string="{}" />
        </div>
    </Card>
</template>

<script>
import tap from "lodash/tap";
import each from "lodash/each";
import { Inertia } from "@inertiajs/inertia";
export default {
    props: ["card", "resource", "resourceId", "resourceName"],
    data: () => ({
        state: 0,
        actions: [],
        executing: false,
        dueDateChangeModal: false,
        dueAt: "",
        originalHandler: null,
    }),
    async mounted() {
        await this.reloadStatus();
        this.state.transition = [...this.state.transitions.filter((item) => item.userInteraction === true)];
        this.dueAt = this.state.dueAt || "";

        this.getActions();

        this.originalHandler = this.$refs.actionSelector.handleActionResponse;

        this.overwriteActionHandler();
    },
    methods: {
        overwriteActionHandler() {
            if (!this.$refs.actionSelector) {
                return;
            }
            /**
             * Overwrites the nova response handler
             */
            this.$refs.actionSelector.handleActionResponse = async (data, headers) => {
                this.originalHandler(data, headers);
                Inertia.reload();
            };
        },
        /**
         * Get the available actions for the resource.
         */
        getActions() {
            this.actions = [];
            return Nova.request()
                .get("/nova-api/" + this.resourceName + "/actions", {
                    params: {
                        resourceId: this.resourceId,
                        editing: true,
                        editMode: "create",
                        display: "detail",
                    },
                })
                .then((response) => {
                    this.actions = response.data.actions;
                });
        },
        async apply(transition) {
            /**
             * Disable Confirmation on all default workflow action
             */
            console.log(this.$refs.actionSelector.availableActions);
            this.$refs.actionSelector.actions.filter((i) => i.uriKey === "workflow-status-change").map((i) => (i.withoutConfirmation = true));

            this.$refs.actionSelector.selectedActionKey = transition.action;

            /**
             * Removes the 'hidden' transition field so nothing is shown to the end user
             */
            this.$refs.actionSelector.selectedAction.fields = this.$refs.actionSelector.selectedAction.fields.filter((i) => i.name !== "transition");

            /**
             * Inject the 'transition' parameter in the handle request
             */
            this.$refs.actionSelector.actionFormData = () => {
                return tap(new FormData(), (formData) => {
                    formData.append("resources", this.$refs.actionSelector.selectedResources);
                    formData.append("transition", transition.name);

                    each(this.$refs.actionSelector.selectedAction.fields, (field) => {
                        field.fill(formData);
                    });
                });
            };

            /**
             * Open the Action modal or call the nova api directly
             */
            this.$refs.actionSelector.determineActionStrategy();
        },
        async reloadStatus() {
            this.state = (await Nova.request().get(`/nova-vendor/nova-workflow/workflow?resourceName=${this.resourceName}&resourceId=${this.resourceId}`)).data;
        },
    },
};
</script>
<style scoped>
.action-selector > div {
    visibility: hidden;
    display: none;
}
.action-selector > div.modal {
    visibility: visible;
    display: block;
}
</style>
