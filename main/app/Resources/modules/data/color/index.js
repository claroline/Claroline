
import {ColorCell} from '#/main/app/data/color/components/cell'
import {ColorGroup} from '#/main/core/layout/form/components/group/color-group'

const dataType = {
  name: 'color',
  components: {
    form: ColorGroup,
    table: ColorCell
  }
}

export {
  dataType
}
