import {trans} from '#/main/app/intl'

const MODE_AUTO = 'auto'
const MODE_THEME = 'theme'
const MODE_DARK = 'dark'
const MODE_LIGHT = 'light'

const MODES = {
  [MODE_AUTO]: trans('theme_mode_auto', {}, 'appearance'),
  /*[MODE_THEME]: trans('theme_mode_theme', {}, 'appearance'),*/
  [MODE_LIGHT]: trans('theme_mode_light', {}, 'appearance'),
  [MODE_DARK]: trans('theme_mode_dark', {}, 'appearance')
}

const FONT_SIZE_AUTO = null
const FONT_SIZE_THEME = 'theme'
const FONT_SIZE_SM = '14px'
const FONT_SIZE_MD = '16px'
const FONT_SIZE_LG = '18px'

const FONT_SIZES = {
  [FONT_SIZE_AUTO]: trans('font_size_auto', {}, 'appearance'),
  /*[FONT_SIZE_THEME]: trans('font_size_theme', {}, 'appearance'),*/
  [FONT_SIZE_SM]: trans('font_size_sm', {}, 'appearance'),
  [FONT_SIZE_MD]: trans('font_size_md', {}, 'appearance'),
  [FONT_SIZE_LG]: trans('font_size_lg', {}, 'appearance')
}

const FONT_WEIGHT_THEME  = 'theme'
const FONT_WEIGHT_LIGHT  = 300
const FONT_WEIGHT_NORMAL = 400
const FONT_WEIGHT_MEDIUM = 500

const FONT_WEIGHTS = {
  //[FONT_WEIGHT_THEME]: trans('font_weight_theme', {}, 'appearance'),
  [FONT_WEIGHT_LIGHT]: trans('font_weight_light', {}, 'appearance'),
  [FONT_WEIGHT_NORMAL]: trans('font_weight_normal', {}, 'appearance'),
  [FONT_WEIGHT_MEDIUM]: trans('font_weight_medium', {}, 'appearance'),
}

export const constants = {
  MODES,
  MODE_AUTO,
  MODE_DARK,
  MODE_LIGHT,

  FONT_SIZES,
  FONT_SIZE_AUTO,
  FONT_SIZE_SM,
  FONT_SIZE_MD,
  FONT_SIZE_LG,

  FONT_WEIGHTS,
  FONT_WEIGHT_LIGHT,
  FONT_WEIGHT_NORMAL,
  FONT_WEIGHT_MEDIUM
}
