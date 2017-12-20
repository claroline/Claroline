
import {connectPage} from '#/main/core/layout/page/connect'
import {Page} from '#/main/core/layout/page/components/page.jsx'

/**
 * Connected container for pages.
 *
 * Connects the <Page> component to a redux store.
 * If you don't use redux in your implementation @see Page functional component.
 *
 * To use with `makePageReducer()`
 *
 * @param props
 * @constructor
 */
const PageContainer = connectPage()(Page)

export {
  PageContainer
}
