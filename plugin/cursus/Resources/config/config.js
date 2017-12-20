var coursesManagement = function (userId) {
  return Routing.generate('claro_cursus_user_sessions_management', {user: userId})
}

module.exports = {
  actions: [
    {
      icon: 'fa fa-fw fa-list-alt',
      name: (Translator) => Translator.trans('open_courses_management', {}, 'cursus'),
      url: coursesManagement,
      type: 'administration_users'
    }
  ]
}
