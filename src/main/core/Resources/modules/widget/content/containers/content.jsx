import {connect} from 'react-redux'

// the component to connect
import {WidgetContent as WidgetContentComponent} from '#/main/core/widget/content/components/content'
// the store to use
import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

const WidgetContent = connect(
  (state) => ({
    config: configSelectors.config(state),
    security: securitySelectors.security(state)
  })
)(WidgetContentComponent)

export {
  WidgetContent
}
