import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {useLocaleStorage} from '#/main/app/storage'
import {Action, PromisedAction} from '#/main/app/action/prop-types'
import {PageBody, PageSimple} from '#/main/app/page'

import {selectors} from '#/main/app/context/store'
import {ContextMenu} from '#/main/app/context/components/menu'
import {PageMenu} from '#/main/app/page/components/menu'
import {PageBanner} from '#/main/app/page/components/banner'

const ContextPage = ({
  className,
  title,
  description,
  children,
  menu,
  banner = null,
  styles = [],
  embedded = false,
  showHeader = true,
  breadcrumb = []
}) => {
  const contextName = useSelector(selectors.name)
  const contextType = useSelector(selectors.type)
  const contextData = useSelector(selectors.data)
  const contextPath = useSelector(selectors.path)
  const contextError = useSelector(selectors.error)

  const [pinedMenu] = useLocaleStorage('contextMenuPined', false)

  return (
    <PageSimple
      className={className}
      title={title ? title + ' | ' + contextName : contextName}
      description={description || get(contextData, 'meta.description')}
      styles={styles}
      embedded={embedded}
    >
      {(!embedded || showHeader) &&
        <PageMenu
          embedded={embedded}
          {...menu}
          breadcrumb={(contextError || !pinedMenu ? [
            {
              label: get(contextData, 'name') || trans(contextType, {}, 'context'),
              target: contextPath
            }
          ] : []).concat(breadcrumb || [])}
          affix={!contextError ? ContextMenu : null}
        />
      }

      {banner &&
        <PageBanner
          embedded={embedded}
          {...banner}
        />
      }

      <PageBody embedded={embedded}>
        {!embedded &&
          <h1 className="visually-hidden">{title ?
            title :
            get(contextData, 'name') || trans(contextType, {}, 'context')}
          </h1>
        }

        {children}
      </PageBody>
    </PageSimple>
  )
}

ContextPage.propTypes = {
  className: T.string,

  /**
   * Custom data used for document head.
   */
  title: T.string,
  description: T.string,

  /**
   * A list of additional styles to add to the page.
   */
  styles: T.arrayOf(T.string),

  /**
   * Is the current page embedded into another one?
   *
   * @type {bool}
   */
  embedded: T.bool,

  children: T.node,

  /**
   * The path of the page inside the context.
   */
  breadcrumb: T.arrayOf(T.shape({
    label: T.string.isRequired,
    target: T.string
  })),
  showHeader: T.bool,
  banner: T.shape({
    type: T.oneOf(['primary', 'info', 'warning', 'danger']),
    content: T.string.isRequired,
    actions: T.oneOfType([
      // a regular array of actions
      T.arrayOf(T.shape(
        Action.propTypes
      )),
      // a promise that will resolve a list of actions
      T.shape(
        PromisedAction.propTypes
      )
    ])
  }),
  menu: T.shape({
    /**
     * The main navigation elements.
     */
    nav: T.arrayOf(T.shape(
      Action.propTypes
    )),
    toolbar: T.string,

    /**
     * A list of actions.
     */
    actions: T.oneOfType([
      // a regular array of actions
      T.arrayOf(T.shape(
        Action.propTypes
      )),
      // a promise that will resolve a list of actions
      T.shape(
        PromisedAction.propTypes
      )
    ]),
    children: T.node
  })
}

export {
  ContextPage
}
