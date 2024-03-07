import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {reducer, selectors} from '#/plugin/lesson/resources/lesson/modals/chapter/store'
import {ChapterDeleteModal as ChapterDeleteModalComponent} from '#/plugin/lesson/resources/lesson/modals/chapter/components/delete'

const ChapterDeleteModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    () => ({
    })
  )(ChapterDeleteModalComponent)
)

export {
  ChapterDeleteModal
}
