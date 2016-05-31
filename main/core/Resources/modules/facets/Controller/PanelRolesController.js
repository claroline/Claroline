export default class PanelRolesController {
  constructor ($uibModalInstance, platformRoles, panel, form) {
    this.$uibModalInstance = $uibModalInstance
    this.platformRoles = platformRoles
    this.panel = panel
    this.form = form

    //now we initialize the field_facet_roles array
    const panelRoles = panel.panel_facets_role

    //build the fieldFacetRoles array. This could be done on the server side.
    const missingRoles = this.platformRoles.filter(element => {
        let found = false
        panelRoles.forEach(panelRole => {
            if (element.id === panelRole.role.id) found = true
        })
        return !found
    }).forEach(element => {
        panelRoles.push({can_open: false, can_edit: false, role: element})
    })
  }

  onSubmit () {
    this.$uibModalInstance.close(this.panel)
  }
}
