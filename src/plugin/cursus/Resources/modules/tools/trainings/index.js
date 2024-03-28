import {reducer} from '#/plugin/cursus/tools/trainings/store'
import {TrainingsTool} from '#/plugin/cursus/tools/trainings/containers/tool'
import {TrainingsMenu} from '#/plugin/cursus/tools/trainings/containers/menu'

export default {
  component: TrainingsTool,
  menu: TrainingsMenu,
  store: reducer
}
