import {ProgressionWidget} from '#/plugin/analytics/widget/types/progression/containers/widget'
import {ProgressionWidgetParameters} from '#/plugin/analytics/widget/types/progression/components/parameters'

export const Parameters = () => ({
  component: ProgressionWidgetParameters
})

/**
 * Progression widget application.
 */
export const App = () => ({
  component: ProgressionWidget
})
