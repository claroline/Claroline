
import {connectPage} from '#/main/core/layout/page/connect'
import {RoutedPage} from '#/main/core/layout/router/components/page.jsx'

/**
 * Connected container for routed pages.
 *
 * Connects the <Page> component to a redux store.
 * If you don't use redux in your implementation @see Page functional component.
 *
 * To use with `makePageReducer()`
 *
 * @param props
 * @constructor
 */
const RoutedPageContainer = connectPage()(RoutedPage)

export {
  RoutedPageContainer
}
