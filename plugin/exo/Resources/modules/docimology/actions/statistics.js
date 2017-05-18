import {makeActionCreator} from '#/main/core/utilities/redux'

export const STATISTICS_SET = 'STATISTICS_SET'

export const actions = {}

actions.setStatistics = makeActionCreator(STATISTICS_SET, 'statistics')
