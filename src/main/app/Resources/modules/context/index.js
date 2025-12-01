
import {ContextMain} from '#/main/app/context/containers/main'
import {ContextMenu} from '#/main/app/context/components/menu'
import {ContextPage} from '#/main/app/context/components/page'
import {selectors} from '#/main/app/context/store'
import {route} from '#/main/app/context/routing'

function declareContext(name, path, ContextComponent) {
  return {
    name: name,
    path: path,
    component: ContextComponent
  }
}

export {
  ContextMain,
  ContextMenu,
  ContextPage,
  selectors,
  route,
  declareContext
}
