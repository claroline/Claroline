import {DataSeries as BarDataSeries} from '#/main/core/layout/chart/bar/components/data-series'
import {DataSeries as LineDataSeries} from '#/main/core/layout/chart/line/components/data-series'
import {DataSeries as PieDataSeries} from '#/main/core/layout/chart/pie/components/data-series'

export const AXIS_POSITION_LEFT = 'left'
export const AXIS_POSITION_TOP = 'top'
export const AXIS_POSITION_RIGHT = 'right'
export const AXIS_POSITION_BOTTOM = 'bottom'
export const AXIS_POSITION_MIDDLE = 'middle'

export const AXIS_TYPE_X = 'x'
export const AXIS_TYPE_Y = 'y'

export const LINE_CHART = 'LINE'
export const BAR_CHART = 'BAR'
export const PIE_CHART = 'PIE'
export const GAUGE_CHART = 'GAUGE'

export const DATE_DATA_TYPE = 'DATE'
export const NUMBER_DATA_TYPE = 'NUMBER'
export const STRING_DATA_TYPE = 'STRING'

export const CHART_TYPES = [LINE_CHART, BAR_CHART, PIE_CHART, GAUGE_CHART]

export const DATA_SERIES = {
  LINE: LineDataSeries,
  BAR: BarDataSeries,
  PIE: PieDataSeries
}