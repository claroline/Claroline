
import {ColorCell} from '#/main/app/data/types/color/components/cell'
import {ColorInput} from '#/main/app/data/types/color/components/input'

const dataType = {
  name: 'color',
  components: {
    input: ColorInput,
    table: ColorCell
  }
}

export {
  dataType
}
