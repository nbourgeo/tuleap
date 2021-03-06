export default MainCtrl;

MainCtrl.$inject = [
    '$element',
    'gettextCatalog',
    'SharedPropertiesService',
    'amMoment',
    'UUIDGeneratorService',
    'FilterTrackerReportService'
];

function MainCtrl(
    $element,
    gettextCatalog,
    SharedPropertiesService,
    amMoment,
    UUIDGeneratorService,
    FilterTrackerReportService
) {
    this.$onInit = init;

    function init() {
        const kanban_init_data = $element[0].querySelector('.kanban-init-data').dataset;

        const user_id = kanban_init_data.userId;
        SharedPropertiesService.setUserId(user_id);
        const kanban_representation = JSON.parse(kanban_init_data.kanban);
        SharedPropertiesService.setKanban(kanban_representation);
        const dashboard_dropdown_representation = JSON.parse(kanban_init_data.dashboardDropdown);
        SharedPropertiesService.setDashboardDropdown(dashboard_dropdown_representation);
        const user_is_admin = Boolean(JSON.parse(kanban_init_data.userIsAdmin));
        SharedPropertiesService.setUserIsAdmin(user_is_admin);
        const widget_id = JSON.parse(kanban_init_data.widgetId);
        SharedPropertiesService.setWidgetId(widget_id);
        const project_id = kanban_init_data.projectId;
        SharedPropertiesService.setProjectId(project_id);
        const view_mode = kanban_init_data.viewMode;
        SharedPropertiesService.setViewMode(view_mode);
        const kanban_url = kanban_init_data.kanbanUrl;
        SharedPropertiesService.setKanbanUrl(kanban_url);

        const tracker_reports = Object.values(JSON.parse(kanban_init_data.trackerReports));
        FilterTrackerReportService.initTrackerReports(tracker_reports);

        const language = kanban_init_data.language;
        gettextCatalog.setCurrentLanguage(language);
        amMoment.changeLocale(language);

        const uuid = UUIDGeneratorService.generateUUID();
        SharedPropertiesService.setUUID(uuid);
        SharedPropertiesService.setNodeServerVersion("1.1.0");
        const nodejs_server = kanban_init_data.nodejsServer;
        SharedPropertiesService.setNodeServerAddress(nodejs_server);
    }
}
