import {ColorPicker} from '#/main/core/layout/form/components/field/color-picker.jsx'

import {ColorGroup} from '#/main/core/layout/form/components/group/color-group'

const COLOR_TYPE = 'color'

const colorDefinition = {
  meta: {
    type: COLOR_TYPE
  },
  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw) => raw,
  validate: (value) => typeof value === 'string',
  components: {
    form: ColorPicker
  }
}

export {
  COLOR_TYPE,
  colorDefinition
}
