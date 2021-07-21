
import {selectors as cursusSelectors} from '#/plugin/cursus/tools/trainings/store/selectors'

const STORE_NAME = cursusSelectors.STORE_NAME + '.quota'
const LIST_NAME = STORE_NAME + '.quotas'
const FORM_NAME = STORE_NAME + '.quotaForm'

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  FORM_NAME
}
