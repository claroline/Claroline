import {connect} from 'react-redux'

import {ImportDetails as ImportDetailsComponent} from '#/main/transfer/tools/transfer/import/components/details'
import {selectors} from '#/main/transfer/tools/transfer/import/store'

const ImportDetails = connect(
  state => ({
    importFile: selectors.importFile(state)
  })
)(ImportDetailsComponent)

export {
  ImportDetails
}
