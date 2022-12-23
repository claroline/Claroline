import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store/actions'

import {VideoResource as VideoResourceComponent} from '#/integration/peertube/resources/video/components/resource'
import {selectors} from '#/integration/peertube/resources/video/store'

const VideoResource = connect(
  (state) => ({
    video: selectors.video(state)
  }),
  (dispatch) => ({
    resetForm(formData) {
      dispatch(formActions.resetForm(selectors.FORM_NAME, formData))
    }
  })
)(VideoResourceComponent)

export {
  VideoResource
}
