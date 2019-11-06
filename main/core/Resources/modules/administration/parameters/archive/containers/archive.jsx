import {connect} from 'react-redux'

import {selectors} from '#/main/core/administration/parameters/store'
import {Archive as ArchiveComponent} from '#/main/core/administration/parameters/archive/components/archive'

const Archive = connect(
  (state) => ({
    archives: selectors.archives(state)
  })
)(ArchiveComponent)

export {
  Archive
}
