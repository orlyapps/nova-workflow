<template>
    <card class="px-4 py-4">
        <div class="flex mb-3 relative">
            <h3 class="mr-3 text-base text-80 font-bold">{{ __("Current status") }}</h3>
            <span
                v-if="state.dueIn"
                class="whitespace-no-wrap px-2 py-1 rounded-full uppercase text-xs font-bold absolute pin-t pin-r"
                :class="{ 'bg-orange-light text-orange-dark': state.duePast === false, 'bg-red-light text-red-dark': state.duePast === true }"
            >
                {{ __("Due") }} {{ state.dueIn }}
            </span>
        </div>
        <div class="flex items-center mb-6">
            <span class="w-4 h-4 block rounded-full mr-3 bg-blue" :class="'bg-' + state.color"></span>
            <div class="flex items-center justify-between w-full">
                <h2 dusk="workflow-current-status">{{ state.title }}</h2>
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
        <div class="mb-6" v-if="state && state.responsibleUsers.length !== 0">
            <h3 class="mr-3 text-base text-80 mb-2">{{ __("Responsibility") }}</h3>
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
                        class="no-underline font-bold dim text-primary"
                    >
                        {{ user.name }}
                    </router-link>
                    ,&nbsp;
                </span>
            </h5>
        </div>

        <div v-if="state.transitions && state.transitions.length">
            <h3 class="mr-3 text-base text-80 mb-2">{{ __("Next Step") }}</h3>
            <p class="text-80 mb-4 text-sm w-1/2" v-if="state.description">{{ state.description }}</p>
            <div v-for="transition in state.transitions" :key="transition.name">
                <a
                    href
                    class="btn btn-sm flex btn-outline items-center mb-2 block"
                    :dusk="'workflow-apply-' + transition.name"
                    @click.stop.prevent="apply(transition)"
                    v-if="transition.userInteraction"
                >
                    {{ transition.title }}
                </a>
            </div>
        </div>
        <action-selector
            v-if="resource"
            ref="actionSelector"
            :resource-name="resourceName"
            :actions="actions"
            :pivot-actions="{ actions: [] }"
            :selected-resources="[this.resourceId]"
            :query-string="{}"
            class="action-selector ml-3"
        />
        <portal to="modals">
            <transition name="fade">
                <modal @modal-close="handleClose" v-if="dueDateChangeModal" class-whitelist="flatpickr-calendar">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden" style="width:500px">
                        <heading :level="2" class="border-b border-40 py-8 px-8">{{ __("Change due date") }}</heading>
                        <slot>
                            <default-field :field="{ attribute: 'due_in', name: __('Due on') }" :fullWidthContent="true">
                                <template slot="field">
                                    <date-time-picker
                                        class="w-full form-control form-input form-input-bordered"
                                        name="due_in"
                                        :value="dueAt"
                                        dateFormat="d.m.Y"
                                        :enable-time="false"
                                        :enable-seconds="false"
                                        :first-day-of-week="1"
                                        @change="onDueChange"
                                    />
                                    <a v-if="dueAt" href class="no-underline font-bold dim text-primary pt-3 block" @click.prevent.stop="clearDue">{{ __("Remove due date") }}</a>
                                </template>
                            </default-field>
                            <div class="bg-30 px-6 py-3 flex">
                                <div class="flex items-center ml-auto">
                                    <button type="button" @click.prevent="handleClose" class="btn text-80 font-normal h-9 px-3 mr-3 btn-link">{{ __("Cancel") }}</button>

                                    <button type="submit" @click.prevent="updateDue" class="btn btn-default btn-primary">
                                        <span>{{ __("Save") }}</span>
                                    </button>
                                </div>
                            </div>
                        </slot>
                    </div>
                </modal>
            </transition>
        </portal>
    </card>
</template>

<script>
export default {
    props: ["card", "resource", "resourceId", "resourceName"],

    data: () => {
        return {
            state: 0,
            actions: [],
            executing: false,
            dueDateChangeModal: false,
            dueAt: "",
            originalHandler: null,
        };
    },

    async mounted() {
        await this.reloadStatus();
        this.state.transition = [...this.state.transitions.filter((item) => item.userInteraction === true)];
        this.dueAt = this.state.dueAt || "";

        this.getActions();

        this.originalHandler = this.$refs.actionSelector.handleActionResponse;

        this.overwriteActionHandler();
        Nova.$on("resources-loaded", async () => {
            await this.reloadStatus();
        });
    },
    destroyed() {
        Nova.$off("resources-loaded");
    },
    methods: {
        overwriteActionHandler() {
            if (!this.$refs.actionSelector) {
                return;
            }
            /**
             * Overwrites the nova response handler
             */
            this.$refs.actionSelector.handleActionResponse = async (response) => {
                this.getActions();
                this.reloadDetailView();
                this.reloadStatus();
                this.originalHandler(response);
                Nova.$emit("resources-loaded");
            };
        },
        clearDue() {
            this.dueAt = "";
            this.updateDue();
        },
        changeDue() {
            this.dueDateChangeModal = true;
        },
        onDueChange(value) {
            this.dueAt = value;
        },
        async updateDue() {
            try {
                this.state = (
                    await Nova.request().put(
                        `/nova-vendor/nova-workflow/workflow/${this.state.lastLog.id}/?resourceName=${this.resourceName}&resourceId=${this.resourceId}&dueAt=${this.dueAt}`
                    )
                ).data;
            } catch (error) {
                this.$toasted.show(__("Error when changing the due date"), { type: "error" });
                return;
            }

            this.$toasted.show(__("Due date successfully updated"), { type: "success" });
            this.dueDateChangeModal = false;
            this.reloadDetailView();
        },
        async apply(transition) {
            /**
             * Disable Confirmation on all default workflow action
             */
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
                return _.tap(new FormData(), (formData) => {
                    formData.append("resources", this.$refs.actionSelector.selectedResources);
                    formData.append("transition", transition.name);
                    _.each(this.$refs.actionSelector.selectedAction.fields, (field) => {
                        field.fill(formData);
                    });
                });
            };

            /**
             * Open the Action modal or call the nova api directly
             */
            this.$refs.actionSelector.determineActionStrategy();
        },
        /**
         * Get the available actions for the resource.
         */
        async getActions() {
            this.actions = [];
            return Nova.request()
                .get("/nova-api/" + this.resourceName + "/actions", {
                    params: {
                        resourceId: this.resourceId,
                    },
                })
                .then((response) => {
                    this.actions = response.data.actions;
                });
        },

        async reloadStatus() {
            this.state = (await Nova.request().get(`/nova-vendor/nova-workflow/workflow?resourceName=${this.resourceName}&resourceId=${this.resourceId}`)).data;
        },
        reloadDetailView() {
            for (const component of this.$root.$children) {
                if (component.cards) {
                    component.initializeComponent();
                }
            }
            setTimeout(() => {
                this.overwriteActionHandler();
            }, 500);
        },
        /**
         * Close the modal.
         */
        handleClose() {
            this.dueDateChangeModal = false;
        },
    },
};
</script>
<style>
.action-selector > div {
    visibility: hidden;
    display: none;
}
.action-selector > div.modal {
    visibility: visible;
    display: block;
}
</style>
<style scoped>
.card-panel {
    height: 100% !important;
    min-height: 150px;
}
.action-selector > div {
    visibility: hidden;
    display: none;
}
.action-selector > div.modal {
    visibility: visible;
    display: block;
}
</style>
