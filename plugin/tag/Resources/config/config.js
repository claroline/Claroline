module.exports = {
  actions: [
    {
      type: 'administration_workspaces',
      icon: 'fa fa-fw fa-tags',
      name: (Translator) => Translator.trans('tag_action', {}, 'tag'),
      url: (workspaceId) => Routing.generate('claro_tag_workspace_tag_form', {workspace: workspaceId}),
      options: {
        modal: true
      }
    }
  ]
}
