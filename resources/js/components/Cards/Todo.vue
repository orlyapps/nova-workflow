<template>
    <div class="card">
        <div class="px-6 pt-4">
            <div class="flex mb-2">
                <h3 class="mr-3 text-base text-80 font-bold">{{ __("My tasks") }}</h3>
            </div>
        </div>
        <div
            class="px-6 pt-4 h-full flex justify-center items-center flex-col"
            v-if="todos.length === 0"
        >
            <svg
                class="h-24 w-24 mb-6 text-50"
                aria-hidden="true"
                focusable="false"
                data-prefix="fad"
                data-icon="smile-beam"
                role="img"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 496 512"
            >
                <g class="fa-group">
                    <path
                        class="fa-secondary"
                        fill="currentColor"
                        d="M245.47,8C108.5,9.43-1.36,121.56,0,258.53S113.56,505.36,250.53,504,497.36,390.44,496,253.47,382.44,6.64,245.47,8ZM112,223.4c3.3-42.1,32.2-71.38,56-71.38s52.7,29.28,56,71.38c.7,8.6-10.8,11.9-14.9,4.5l-9.5-17c-7.7-13.7-19.2-21.6-31.5-21.6s-23.78,7.9-31.48,21.6l-9.5,17C122.82,235.3,111.32,231.9,112,223.4ZM363.69,345a149.34,149.34,0,0,1-229.54,2.35c-13.67-16.17,10.62-36.81,24.37-20.75a117.57,117.57,0,0,0,180.39-1.84c13.41-16.32,38.08,3.93,24.78,20.24ZM369,227.9l-9.5-17c-7.7-13.7-19.18-21.6-31.48-21.6s-23.8,7.9-31.5,21.6l-9.5,17c-4.1,7.3-15.6,4-14.9-4.5,3.3-42.1,32.2-71.38,56-71.38s52.68,29.28,56,71.38c.58,8.6-11,11.9-15.11,4.5Z"
                        opacity="0.4"
                    />
                    <path
                        class="fa-primary"
                        fill="currentColor"
                        d="M168,152c-23.78,0-52.68,29.28-56,71.38-.7,8.5,10.8,11.9,15.1,4.5l9.5-17c7.7-13.7,19.18-21.6,31.48-21.6s23.8,7.9,31.5,21.6l9.5,17c4.1,7.4,15.6,4.1,14.9-4.5C220.7,181.3,191.8,152,168,152ZM384.08,223.4c-3.3-42.1-32.2-71.38-56-71.38s-52.7,29.28-56,71.38c-.7,8.5,10.8,11.8,14.9,4.5l9.5-17c7.7-13.7,19.2-21.6,31.5-21.6s23.78,7.9,31.48,21.6l9.5,17c4.11,7.4,15.71,4.1,15.11-4.5Z"
                    />
                </g>
            </svg>

            <h2 class="mb-6 text-90">{{ __("All done!") }}</h2>
        </div>
        <div v-for="(items, group) in todos" :key="group">
            <div class="px-6">
                <div class="flex mb-4 mt-4">
                    <p class="flex items-center text-xl">{{ group }}</p>
                </div>
            </div>

            <div class="border-t border-50">
                <div
                    class="item px-6 py-4 border-b border-50 hover:bg-20 hover:cursor-pointer w-full"
                    v-for="item in items"
                    :dusk="'todo-item-' + item.id"
                    @click="onAction(item)"
                    :key="item.id"
                >
                    <div class="flex items-center w-full">
                        <span class="w-4 h-4 block rounded-full mr-3" :class="'bg-' + item.color"></span>
                        <div class="w-full">
                            <div class="flex items-center justify-between w-full">
                                <h4
                                    class="mb-1 text-xs text-80 uppercase tracking-wide"
                                >{{ item.model }}</h4>
                                <span
                                    v-if="item.dueIn"
                                    :class="{'bg-orange-light text-orange-dark': item.duePast === false, 'bg-red-light text-red-dark' : item.duePast === true}"
                                    class="px-3 py-2 rounded-full uppercase text-xs font-bold"
                                >{{ __("Due") }} {{ item.dueFormatted }}</span>
                            </div>
                            <h4
                                class="text-base mb-1"
                                :class="{'text-orange-dark': item.duePast === false, 'text-red-dark' : item.duePast === true}"
                            >{{ item.title }}</h4>
                            <p class="text-base" v-html="item.subtitle"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["card", "resource", "resourceId", "resourceName"],
    data: () => {
        return {
            todos: [],
        };
    },

    async mounted() {
        this.onLoad();
        Nova.$on("cards.refresh", () => {
            this.onLoad();
        });
    },
    methods: {
        onAction(item) {
            this.$router.push({ path: item.path });
        },
        async onLoad() {
            this.todos = (await Nova.request().get(`/nova-vendor/nova-workflow/todos/?providers=${this.card.providers.join(",")}`)).data;
        },
    },
};
</script>

<style scoped>
.item:hover {
    cursor: pointer;
}
.card-panel {
    height: auto !important;
    min-height: 150px;
}
</style>
