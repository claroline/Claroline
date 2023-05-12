import {registry} from '#/main/app/modals/registry'

import {ColorChartEditModal} from '#/main/theme/administration/appearance/modals/color-chart-edit/containers/modal'

const MODAL_EDIT_COLOR_CHART = 'MODAL_EDIT_COLOR_CHART'

registry.add(MODAL_EDIT_COLOR_CHART, ColorChartEditModal)

export {
  MODAL_EDIT_COLOR_CHART
}
