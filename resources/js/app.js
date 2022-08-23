import Card from "./components/Cards/Workflow";

Nova.booting((app,  store) => {
    app.component("workflow-card", Card);
});
