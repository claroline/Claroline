module.exports = {
  actions: [
    {
      type: 'administration_users',
      icon: 'fa fa-fw fa-list-alt',
      name: (Translator) => Translator.trans('open_courses_management', {}, 'cursus'),
      url: (userId) => Routing.generate('claro_cursus_user_sessions_management', {user: userId})
    }
  ]
}
