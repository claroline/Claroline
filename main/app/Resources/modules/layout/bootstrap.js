import {bootstrap} from '#/main/app/dom/bootstrap'

import LayoutApp from '#/main/app/layout'

// for dev purpose. This allows us to have an understandable name in
// the list of stores in the dev tools
LayoutApp.component.displayName = 'MainApp'

// mount the react application
bootstrap('.app-container', LayoutApp.component, LayoutApp.store, LayoutApp.initialData)
