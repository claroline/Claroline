import {registry} from '#/main/app/modals/registry'

import {ColorChartCreationModal} from '#/main/theme/administration/appearance/modals/color-chart-creation/containers/modal'

const MODAL_NEW_COLOR_CHART = 'MODAL_NEW_COLOR_CHART'

registry.add(MODAL_NEW_COLOR_CHART, ColorChartCreationModal)

export {
  MODAL_NEW_COLOR_CHART
}
