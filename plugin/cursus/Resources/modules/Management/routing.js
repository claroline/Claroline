/*global Translator*/

import rootCursusManagementTemplate from './Partial/root_cursus_management.html'
import cursusManagementTemplate from './Partial/cursus_management.html'
import coursesManagementTemplate from './Partial/courses_management.html'
import sessionsManagementTemplate from './Partial/sessions_management.html'
import courseTemplate from './Partial/course_management.html'
import sessionTemplate from './Partial/session_management.html'
import sessionEventTemplate from './Partial/session_event_management.html'
import configurationTemplate from './Partial/configuration.html'
import parametersTemplate from './Partial/general_parameters.html'
import locationsManagementTemplate from './Partial/locations_management.html'
import documentModelsManagementTemplate from './Partial/document_models_management.html'
import documentModelFormTemplate from './Partial/document_model_form.html'
import certificateMailEditionTemplate from './Partial/certificate_mail_form.html'

export default function ($stateProvider, $urlRouterProvider) {
  $stateProvider
    .state ('root_cursus_management', {
      url: '/cursus',
      template: rootCursusManagementTemplate,
      controller: 'RootCursusManagementCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: Translator.trans('cursus_management', {}, 'cursus')
      }
    })
    .state ('cursus', {
      url: '/cursus/{cursusId}',
      template: cursusManagementTemplate,
      controller: 'CursusManagementCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: '{{ cmc.breadCrumbLabel }}',
        parent: 'root_cursus_management'
      }
    })
    .state ('courses_management', {
      url: '/courses',
      template: coursesManagementTemplate,
      controller: 'CoursesManagementCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: Translator.trans('courses_management', {}, 'cursus')
      }
    })
    .state ('sessions_management', {
      url: '/sessions',
      template: sessionsManagementTemplate,
      controller: 'SessionsManagementCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: Translator.trans('sessions_management', {}, 'cursus')
      }
    })
    .state ('course', {
      url: '/courses/{courseId}',
      template: courseTemplate,
      controller: 'CourseManagementCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: '{{ cmc.breadCrumbLabel }}',
        parent: 'courses_management'
      }
    })
    .state ('session', {
      url: '/sessions/{sessionId}',
      template: sessionTemplate,
      controller: 'SessionManagementCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: '{{ cmc.breadCrumbLabel }}',
        parent: 'sessions_management'
      }
    })
    .state ('session_event', {
      url: '/sessions/{sessionId}/event/{sessionEventId}',
      template: sessionEventTemplate,
      controller: 'SessionEventManagementCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: '{{ cmc.breadCrumbLabelEvent }}',
        parent: 'session({sessionId: cmc.sessionId})'
      }
    })
    .state ('configuration', {
      url: '/configuration',
      template: configurationTemplate,
      ncyBreadcrumb: {
        label: Translator.trans('configuration', {}, 'platform')
      }
    })
    .state ('general_parameters', {
      url: '/configuration/parameters',
      template: parametersTemplate,
      controller: 'GeneralParametersCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: Translator.trans('general_parameters', {}, 'cursus'),
        parent: 'configuration'
      }
    })
    .state ('locations_management', {
      url: '/configuration/locations',
      template: locationsManagementTemplate,
      controller: 'LocationsManagementCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: Translator.trans('locations_management', {}, 'cursus'),
        parent: 'configuration'
      }
    })
    .state ('document_models_management', {
      url: '/configuration/documents',
      template: documentModelsManagementTemplate,
      controller: 'DocumentModelsManagementCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: Translator.trans('document_models_management', {}, 'cursus'),
        parent: 'configuration'
      }
    })
    .state ('document_model_creation', {
      url: '/configuration/documents/creation',
      template: documentModelFormTemplate,
      controller: 'DocumentModelCreationCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: Translator.trans('document_model_creation', {}, 'cursus'),
        parent: 'document_models_management'
      }
    })
    .state ('document_model_edition', {
      url: '/configuration/documents/{modelId}/edition',
      template: documentModelFormTemplate,
      controller: 'DocumentModelEditionCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: Translator.trans('document_model_edition', {}, 'cursus'),
        parent: 'document_models_management'
      }
    })
    .state ('certificate_mail_edition', {
      url: '/configuration/certificate/email',
      template: certificateMailEditionTemplate,
      controller: 'CertificateMailEditionCtrl',
      controllerAs: 'cmc',
      ncyBreadcrumb: {
        label: Translator.trans('certificate_mail_edition', {}, 'cursus'),
        parent: 'configuration'
      }
    })

  $urlRouterProvider.otherwise('/courses')
}
