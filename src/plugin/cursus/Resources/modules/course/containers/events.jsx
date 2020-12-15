import {connect} from 'react-redux'

import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CourseEvents as CourseEventsComponent} from '#/plugin/cursus/course/components/events'

const CourseEvents = connect(
  null,
  (dispatch) => ({
    invalidateList() {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.courseEvents'))
    }
  })
)(CourseEventsComponent)

export {
  CourseEvents
}
