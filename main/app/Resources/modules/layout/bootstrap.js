import {bootstrap} from '#/main/app/dom/bootstrap'

import LayoutApp from '#/main/app/layout'

// mount the react application
bootstrap('.app-container', LayoutApp.component, LayoutApp.store, LayoutApp.initialData)
