import {makeActionCreator} from '#/main/app/store/actions'

export const DOCUMENT_UPDATE = 'DOCUMENT_UPDATE'

export const actions = {}

actions.updateDocument = makeActionCreator(DOCUMENT_UPDATE, 'document')
