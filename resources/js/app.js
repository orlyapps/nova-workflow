import Card from "./components/Cards/Workflow";
import WorkflowActivity from "./components/ResourceTools/Workflow/Tool";
import Status from "./components/ResourceTools/Workflow/Status";
import WriteComment from "./components/ResourceTools/Workflow/WriteComment";


Nova.booting((app,  store) => {
    app.component("workflow-card", Card);
    app.component("workflow-activity", WorkflowActivity);
    app.component("workflow-status", Status);
    app.component("workflow-write-comment", WriteComment);
});
