require("./components/ResourceTools/Workflow");

Nova.booting((Vue, router, store) => {
    Vue.component("workflow-card", require("./components/Cards/Workflow").default);
    Vue.component("todo-card", require("./components/Cards/Todo").default);
});
