import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'

import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'

export const DROP_UPDATE = 'DROP_UPDATE'
export const CURRENT_DROP_LOAD = 'CURRENT_DROP_LOAD'
export const CURRENT_DROP_RESET = 'CURRENT_DROP_RESET'
export const CURRENT_DROP_COMMENT_UPDATE = 'CURRENT_DROP_COMMENT_UPDATE'
export const CORRECTOR_DROP_LOAD = 'CORRECTOR_DROP_LOAD'
export const CORRECTOR_DROP_RESET = 'CORRECTOR_DROP_RESET'
export const CORRECTIONS_LOAD = 'CORRECTIONS_LOAD'
export const CORRECTION_UPDATE = 'CORRECTION_UPDATE'
export const CORRECTION_REMOVE = 'CORRECTION_REMOVE'

export const actions = {}

actions.updateDrop = makeActionCreator(DROP_UPDATE, 'drop')

actions.loadCurrentDrop = makeActionCreator(CURRENT_DROP_LOAD, 'drop')
actions.resetCurrentDrop = makeActionCreator(CURRENT_DROP_RESET)
actions.updateCurrentDropComment = makeActionCreator(CURRENT_DROP_COMMENT_UPDATE, 'comment')

actions.fetchDrop = (dropId, type = 'current') => (dispatch, getState) => {
  const dropsData = selectors.drops(getState())
  let drop = null

  if (dropsData.data) {
    drop = dropsData.data.find(d => d.id === dropId)
  }
  if (drop) {
    switch (type) {
      case 'current':
        dispatch(actions.loadCurrentDrop(drop))
        break
      case 'corrector':
        dispatch(actions.loadCorrectorDrop(drop))
        break
    }
  } else {
    dispatch({
      [API_REQUEST]: {
        url: ['claro_dropzone_drop_fetch', {id: dropId}],
        success: (data, dispatch) => {
          switch (type) {
            case 'current':
              dispatch(actions.loadCurrentDrop(data))
              break
            case 'corrector':
              dispatch(actions.loadCorrectorDrop(data))
              break
          }
        }
      }
    })
  }
}

actions.loadCorrectorDrop = makeActionCreator(CORRECTOR_DROP_LOAD, 'drop')
actions.resetCorrectorDrop = makeActionCreator(CORRECTOR_DROP_RESET)

actions.fetchCorrections = (dropzoneId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_corrections_fetch', {id: dropzoneId}],
    success: (data, dispatch) => {
      dispatch(actions.loadCorrections(data))
    }
  }
})

actions.loadCorrections = makeActionCreator(CORRECTIONS_LOAD, 'corrections')
actions.updateCorrection = makeActionCreator(CORRECTION_UPDATE, 'correction')
actions.removeCorrection = makeActionCreator(CORRECTION_REMOVE, 'correctionId')

actions.saveCorrection = (correction) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_correction_save', {id: correction.drop}],
    request: {
      method: 'POST',
      body: JSON.stringify(correction)
    },
    success: (data, dispatch) => {
      dispatch(actions.updateCorrection(data))
    }
  }
})

actions.submitCorrection = (correctionId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_correction_submit', {id: correctionId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.updateCorrection(data))
    }
  }
})

actions.switchCorrectionValidation = (correctionId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_correction_validation_switch', {id: correctionId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.updateCorrection(data))
    }
  }
})

actions.deleteCorrection = (correctionId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_correction_delete', {id: correctionId}],
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(actions.removeCorrection(correctionId))
    }
  }
})

actions.denyCorrection = (correctionId, comment) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_correction_deny', {id: correctionId}],
    request: {
      method: 'PUT',
      body: JSON.stringify({comment: comment})
    },
    success: (data, dispatch) => {
      dispatch(actions.updateCorrection(data))
    }
  }
})

actions.unlockDrop = (dropId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_drop_unlock', {id: dropId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.updateDrop(data))
    }
  }
})

actions.unlockDropUser = (dropId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_drop_unlock_user', {id: dropId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.updateDrop(data))
    }
  }
})

actions.cancelDropSubmission = (dropId) => ({
  [API_REQUEST]: {
    url: ['claro_dropzone_drop_submission_cancel', {id: dropId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.updateDrop(data))
    }
  }
})

actions.downloadDrops = (drops) => ({
  [API_REQUEST]: {
    url: url(['claro_dropzone_drops_download'], {ids: drops.map(d => d.id)})
  }
})
