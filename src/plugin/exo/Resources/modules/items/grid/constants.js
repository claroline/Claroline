import {trans} from '#/main/app/intl/translation'

const SUM_CELL = 'cell'
const SUM_COL = 'col'
const SUM_ROW = 'row'

const SUM_MODES = {
  [SUM_CELL]: trans('grid_score_sum_cell', {}, 'quiz'),
  [SUM_COL]: trans('grid_score_sum_col', {}, 'quiz'),
  [SUM_ROW]: trans('grid_score_sum_row', {}, 'quiz')
}

export const constants = {
  SUM_CELL,
  SUM_COL,
  SUM_ROW,
  SUM_MODES
}
