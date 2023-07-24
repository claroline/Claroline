import {trans} from '#/main/app/intl/translation'

import {constants as actionConstants} from '#/main/app/action/constants'

const ALERT_DISPLAY_MAX     = 5
const ALERT_DISPLAY_TIMEOUT = 2000

const ALERT_STATUS_SUCCESS      = 'success'
const ALERT_STATUS_WARNING      = 'warning'
const ALERT_STATUS_ERROR        = 'error'
const ALERT_STATUS_INFO         = 'info'
const ALERT_STATUS_PENDING      = 'pending'
const ALERT_STATUS_UNAUTHORIZED = 'unauthorized'
const ALERT_STATUS_UNAVAILABLE  = 'unavailable'
const ALERT_STATUS_FORBIDDEN    = 'forbidden'

const ALERT_STATUS = {
  [ALERT_STATUS_PENDING]: {
    order: 1,
    icon: 'fa-spinner',
    removable: false
  },
  [ALERT_STATUS_FORBIDDEN]: {
    order: 2,
    icon: 'fa-lock',
    removable: true
  },
  [ALERT_STATUS_ERROR]: {
    order: 3,
    icon: 'fa-times',
    removable: true
  },
  [ALERT_STATUS_UNAUTHORIZED]: {
    order: 4,
    icon: 'fa-lock-alt',
    removable: true
  },
  [ALERT_STATUS_UNAVAILABLE]: {
    order: 5,
    icon: 'fa-power-off',
    removable: true,
    timeout: ALERT_DISPLAY_TIMEOUT
  },
  [ALERT_STATUS_WARNING]: {
    order: 6,
    icon: 'fa-exclamation',
    removable: true,
    timeout: ALERT_DISPLAY_TIMEOUT
  },
  [ALERT_STATUS_SUCCESS]: {
    order: 7,
    icon: 'fa-check',
    removable: true,
    timeout: ALERT_DISPLAY_TIMEOUT
  },
  [ALERT_STATUS_INFO]: {
    order: 8,
    icon: 'fa-lightbulb',
    removable: true,
    timeout: ALERT_DISPLAY_TIMEOUT * 3 // increase display time to let users read the message
  }
}

/**
 * The list of status that should be stacked when displayed.
 * (this permits to avoid having lots of loading messages at once)
 * @type {Array}
 */
const ALERT_STACKED_STATUS = [
  ALERT_STATUS_PENDING
]

/**
 * Defines available alerts for the app ACTIONS.
 * NB. If ACTION do not declare one of the ALERT_STATUS, this will disable it.
 *
 * @type {object}
 */
const ALERT_ACTIONS = {
  [actionConstants.ACTION_GENERIC]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('generic.pending.title', {}, 'alerts'),
      message: trans('generic.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_SUCCESS]: {
      title: trans('generic.success.title', {}, 'alerts'),
      message: trans('generic.success.message', {}, 'alerts')
    },
    [ALERT_STATUS_WARNING]: {
      title: trans('generic.warning.title', {}, 'alerts'),
      message: trans('generic.warning.message', {}, 'alerts')
    },
    [ALERT_STATUS_UNAUTHORIZED]: {
      title: trans('generic.unauthorized.title', {}, 'alerts'),
      message: trans('generic.unauthorized.message', {}, 'alerts')
    },
    [ALERT_STATUS_FORBIDDEN]: {
      title: trans('generic.forbidden.title', {}, 'alerts'),
      message: trans('generic.forbidden.message', {}, 'alerts')
    },
    [ALERT_STATUS_UNAVAILABLE]: {
      title: trans('generic.unavailable.title', {}, 'alerts'),
      message: trans('generic.unavailable.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('generic.error.title', {}, 'alerts'),
      message: trans('generic.error.message', {}, 'alerts')
    }
  },
  [actionConstants.ACTION_LOAD]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('load.pending.title', {}, 'alerts'),
      message: trans('load.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('load.error.title', {}, 'alerts'),
      message: trans('load.error.message', {}, 'alerts')
    }
  },
  [actionConstants.ACTION_REFRESH]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('refresh.pending.title', {}, 'alerts'),
      message: trans('refresh.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('refresh.error.title', {}, 'alerts'),
      message: trans('refresh.error.message', {}, 'alerts')
    }
  },
  [actionConstants.ACTION_SAVE]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('save.pending.title', {}, 'alerts'),
      message: trans('save.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_SUCCESS]: {
      title: trans('save.success.title', {}, 'alerts'),
      message: trans('save.success.message', {}, 'alerts')
    },
    [ALERT_STATUS_WARNING]: {
      title: trans('save.warning.title', {}, 'alerts'),
      message: trans('save.warning.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('save.error.title', {}, 'alerts'),
      message: trans('save.error.message', {}, 'alerts')
    }
  },
  [actionConstants.ACTION_CREATE]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('create.pending.title', {}, 'alerts'),
      message: trans('create.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_SUCCESS]: {
      title: trans('create.success.title', {}, 'alerts'),
      message: trans('create.success.message', {}, 'alerts')
    },
    [ALERT_STATUS_WARNING]: {
      title: trans('create.warning.title', {}, 'alerts'),
      message: trans('create.warning.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('create.error.title', {}, 'alerts'),
      message: trans('create.error.message', {}, 'alerts')
    },
    [ALERT_STATUS_FORBIDDEN]: {
      title: trans('create.forbidden.title', {}, 'alerts'),
      message: trans('create.forbidden.message', {}, 'alerts')
    }
  },
  [actionConstants.ACTION_UPDATE]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('update.pending.title', {}, 'alerts'),
      message: trans('update.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_SUCCESS]: {
      title: trans('update.success.title', {}, 'alerts'),
      message: trans('update.success.message', {}, 'alerts')
    },
    [ALERT_STATUS_WARNING]: {
      title: trans('update.warning.title', {}, 'alerts'),
      message: trans('update.warning.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('update.error.title', {}, 'alerts'),
      message: trans('update.error.message', {}, 'alerts')
    },
    [ALERT_STATUS_FORBIDDEN]: {
      title: trans('update.forbidden.title', {}, 'alerts'),
      message: trans('update.forbidden.message', {}, 'alerts')
    }
  },
  [actionConstants.ACTION_DELETE]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('delete.pending.title', {}, 'alerts'),
      message: trans('delete.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_SUCCESS]: {
      title: trans('delete.success.title', {}, 'alerts'),
      message: trans('delete.success.message', {}, 'alerts')
    },
    [ALERT_STATUS_WARNING]: {
      title: trans('delete.warning.title', {}, 'alerts'),
      message: trans('delete.warning.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('delete.error.title', {}, 'alerts'),
      message: trans('delete.error.message', {}, 'alerts')
    },
    [ALERT_STATUS_FORBIDDEN]: {
      title: trans('delete.forbidden.title', {}, 'alerts'),
      message: trans('delete.forbidden.message', {}, 'alerts')
    }
  },
  [actionConstants.ACTION_SEND]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('send.pending.title', {}, 'alerts'),
      message: trans('send.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_SUCCESS]: {
      title: trans('send.success.title', {}, 'alerts'),
      message: trans('send.success.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('send.error.title', {}, 'alerts'),
      message: trans('send.error.message', {}, 'alerts')
    }
  },
  [actionConstants.ACTION_UPLOAD]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('upload.pending.title', {}, 'alerts'),
      message: trans('upload.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_SUCCESS]: {
      title: trans('upload.success.title', {}, 'alerts'),
      message: trans('upload.success.message', {}, 'alerts')
    },
    [ALERT_STATUS_WARNING]: {
      title: trans('upload.warning.title', {}, 'alerts'),
      message: trans('upload.warning.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('upload.error.title', {}, 'alerts'),
      message: trans('upload.error.message', {}, 'alerts')
    },
    [ALERT_STATUS_FORBIDDEN]: {
      title: trans('upload.forbidden.title', {}, 'alerts'),
      message: trans('upload.forbidden.message', {}, 'alerts')
    }
  },
  [actionConstants.ACTION_PUBLISH]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('publish.pending.title', {}, 'alerts'),
      message: trans('publish.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_SUCCESS]: {
      title: trans('publish.success.title', {}, 'alerts'),
      message: trans('publish.success.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('publish.error.title', {}, 'alerts'),
      message: trans('publish.error.message', {}, 'alerts')
    }
  },
  [actionConstants.ACTION_UNPUBLISH]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('unpublish.pending.title', {}, 'alerts'),
      message: trans('unpublish.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_SUCCESS]: {
      title: trans('unpublish.success.title', {}, 'alerts'),
      message: trans('unpublish.success.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('unpublish.error.title', {}, 'alerts'),
      message: trans('unpublish.error.message', {}, 'alerts')
    }
  },
  [actionConstants.ACTION_SCHEDULE]: {
    [ALERT_STATUS_PENDING]: {
      title: trans('schedule.pending.title', {}, 'alerts'),
      message: trans('schedule.pending.message', {}, 'alerts')
    },
    [ALERT_STATUS_INFO]: {
      title: trans('schedule.info.title', {}, 'alerts'),
      message: trans('schedule.info.message', {}, 'alerts')
    },
    [ALERT_STATUS_ERROR]: {
      title: trans('schedule.error.title', {}, 'alerts'),
      message: trans('schedule.error.message', {}, 'alerts')
    }
  }
}

export const constants = {
  // ui config
  ALERT_DISPLAY_MAX,
  ALERT_DISPLAY_TIMEOUT,
  // status
  ALERT_STATUS,
  ALERT_STACKED_STATUS,
  ALERT_STATUS_SUCCESS,
  ALERT_STATUS_WARNING,
  ALERT_STATUS_ERROR,
  ALERT_STATUS_INFO,
  ALERT_STATUS_PENDING,
  ALERT_STATUS_UNAUTHORIZED,
  ALERT_STATUS_FORBIDDEN,
  ALERT_STATUS_UNAVAILABLE,

  ALERT_ACTIONS
}
