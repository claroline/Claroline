import {constants as actionConstants} from '#/main/core/layout/action/constants'

const ALERT_DISPLAY_MAX     = 5
const ALERT_DISPLAY_TIMEOUT = 1000

const ALERT_STATUS_SUCCESS      = 'success'
const ALERT_STATUS_WARNING      = 'warning'
const ALERT_STATUS_ERROR        = 'error'
const ALERT_STATUS_INFO         = 'info'
const ALERT_STATUS_PENDING      = 'pending'
const ALERT_STATUS_UNAUTHORIZED = 'unauthorized'
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
  [ALERT_STATUS_WARNING]: {
    order: 5,
    icon: 'fa-exclamation',
    removable: true
  },
  [ALERT_STATUS_SUCCESS]: {
    order: 6,
    icon: 'fa-check',
    removable: true
  },
  [ALERT_STATUS_INFO]: {
    order: 7,
    icon: 'fa-info',
    removable: true
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
      title: 'Chargement en cours',
      message: 'Veuillez patienter pendant le chargement de vos données.'
    },
    [ALERT_STATUS_SUCCESS]: {
      title: 'Succès'
    },
    [ALERT_STATUS_WARNING]: {
      title: 'Attention'
    },
    [ALERT_STATUS_UNAUTHORIZED]: {
      title: 'Accès non authorisé',
      message: 'Vous devez être connecté pour accéder à cette fonctionnalité.'
    },
    [ALERT_STATUS_FORBIDDEN]: {
      title: 'Accès interdit',
      message: 'Vous n\'avez pas accès à cette fonctionnalité.'
    },
    [ALERT_STATUS_ERROR]: {
      title: 'Erreur',
      message: 'Veuillez contacter un administrateur.'
    },
    [ALERT_STATUS_INFO]: {
      title: 'Information'
    }
  },
  [actionConstants.ACTION_LOAD]: {
    [ALERT_STATUS_PENDING]: {
      title: 'Chargement en cours',
      message: 'Veuillez patienter pendant le chargement de vos données.'
    },
    [ALERT_STATUS_ERROR]: {
      title: 'Echec du chargement',
      message: 'Nous n\'avons pas réussi à charger vos données.'
    }
  },
  [actionConstants.ACTION_REFRESH]: {
    [ALERT_STATUS_PENDING]: {
      title: 'Rechargement en cours',
      message: 'Veuillez patienter pendant le rafraîchissement de vos données.'
    },
    [ALERT_STATUS_ERROR]: {
      title: 'Echec du rechargement',
      message: 'Nous n\'avons pas réussi à rafraîchir vos données.'
    }
  },
  [actionConstants.ACTION_SAVE]: {
    [ALERT_STATUS_PENDING]: {
      title: 'Sauvegarde en cours',
      message: 'Veuillez patienter pendant la sauvegarde de vos données.'
    },
    [ALERT_STATUS_SUCCESS]: {
      title: 'Sauvegarde réussie',
      message: 'Vos données ont correctement été sauvegardée.'
    },
    [ALERT_STATUS_WARNING]: {
      title: 'Echec de la sauvegarde',
      message: 'Veuillez corriger les erreurs de votre formulaire et réessayer.'
    },
    [ALERT_STATUS_ERROR]: {
      title: 'Echec de la sauvegarde',
      message: 'Nous n\'avons pas réussi à charger vos données.'
    }
  },
  [actionConstants.ACTION_CREATE]: {
    [ALERT_STATUS_PENDING]: {
      title: 'Création en cours',
      message: 'Veuillez patienter pendant la sauvegarde de vos données.'
    },
    [ALERT_STATUS_SUCCESS]: {
      title: 'Création réussie',
      message: 'Vos données ont correctement été sauvegardée.'
    },
    [ALERT_STATUS_WARNING]: {
      title: 'Echec de la création',
      message: 'Veuillez corriger les erreurs de votre formulaire et réessayer.'
    },
    [ALERT_STATUS_ERROR]: {
      title: 'Echec de la création'
    }
  },
  [actionConstants.ACTION_UPDATE]: {
    [ALERT_STATUS_PENDING]: {
      title: 'Mise à jour en cours',
      message: 'Veuillez patienter pendant la sauvegarde de vos données.'
    },
    [ALERT_STATUS_SUCCESS]: {
      title: 'Mise à jour réussie',
      message: 'Vos données ont correctement été sauvegardée.'
    },
    [ALERT_STATUS_WARNING]: {
      title: 'Echec de la mise à jour',
      message: 'Veuillez corriger les erreurs de votre formulaire et réessayer.'
    },
    [ALERT_STATUS_ERROR]: {
      title: 'Echec de la mise à jour'
    }
  },
  [actionConstants.ACTION_DELETE]: {
    [ALERT_STATUS_PENDING]: {
      title: 'Suppression en cours',
      message: 'Veuillez patienter pendant la suppression de vos données.'
    },
    [ALERT_STATUS_SUCCESS]: {
      title: 'Suppression réussie',
      message: 'Vos données ont correctement été supprimée.'
    },
    [ALERT_STATUS_WARNING]: {
      title: 'Echec de la suppression'
    },
    [ALERT_STATUS_ERROR]: {
      title: 'Echec de la suppression'
    }
  },
  [actionConstants.ACTION_SEND]: {
    [ALERT_STATUS_PENDING]: {
      title: 'Envoi en cours',
      message: 'Veuillez patienter pendant l\'envoi de votre message.'
    },
    [ALERT_STATUS_SUCCESS]: {
      title: 'Envoi réussi'
    },
    [ALERT_STATUS_WARNING]: {
      title: 'Echec de l\'envoi'
    },
    [ALERT_STATUS_ERROR]: {
      title: 'Echec de l\'envoi'
    }
  },
  [actionConstants.ACTION_UPLOAD]: {
    [ALERT_STATUS_PENDING]: {
      title: 'Téléchargement en cours',
      message: 'Veuillez patienter pendant le téléchargement de votre fichier.'
    },
    [ALERT_STATUS_SUCCESS]: {
      title: 'Téléchargement réussi',
      message: 'Votre fichier a correctement été téléchargé.'
    },
    [ALERT_STATUS_WARNING]: {
      title: 'Echec de l\'envoi'
    },
    [ALERT_STATUS_ERROR]: {
      title: 'Echec de l\'envoi'
    }
  }
}

// remap action on HTTP status code
const HTTP_ALERT_STATUS = {
  // success
  200: ALERT_STATUS_SUCCESS,
  201: ALERT_STATUS_SUCCESS,
  204: ALERT_STATUS_SUCCESS,
  // warning
  401: ALERT_STATUS_UNAUTHORIZED,
  403: ALERT_STATUS_FORBIDDEN,
  422: ALERT_STATUS_WARNING,
  // error
  500: ALERT_STATUS_ERROR
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

  ALERT_ACTIONS,
  // http mapping
  HTTP_ALERT_STATUS
}
