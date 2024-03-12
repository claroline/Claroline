import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {reducer, selectors} from '#/plugin/wiki/resources/wiki/player/modals/section/store'
import {SectionDeleteModal as SectionDeleteModalComponent} from '#/plugin/wiki/resources/wiki/player/modals/section/components/delete'

const SectionDeleteModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    () => ({
    })
  )(SectionDeleteModalComponent)
)

export {
  SectionDeleteModal
}
