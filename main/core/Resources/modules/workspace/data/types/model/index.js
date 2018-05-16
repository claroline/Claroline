import {ModelGroup} from '#/main/core/workspace/data/types/components/model-group.jsx'

const MODEL_TYPE = 'model'

import {t} from '#/main/core/translation'

const modelDefinition = {
  meta: {
    type: MODEL_TYPE,
    creatable: false,
    label: t('model'),
    description: t('model_descr')
  },
  render: (raw) => raw ? raw.name : null,
  components: {
    form: ModelGroup
  }
}

export {
  MODEL_TYPE,
  modelDefinition
}
