import {reducer} from '#/plugin/cursus/tools/trainings/store'
import {TrainingsTool} from '#/plugin/cursus/tools/trainings/components/tool'
import {TrainingsMenu} from '#/plugin/cursus/tools/trainings/components/menu'

export default {
  component: TrainingsTool,
  menu: TrainingsMenu,
  store: reducer
}
