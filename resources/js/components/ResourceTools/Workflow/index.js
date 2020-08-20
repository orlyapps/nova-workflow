Nova.booting((Vue, router, store) => {
    Vue.component("workflow-write-comment", require("./WriteComment").default);
    Vue.component("workflow-status", require("./Status").default);
    Vue.component("workflow-activity", require("./Tool").default);
});
