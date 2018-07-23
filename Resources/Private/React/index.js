import React from 'react';
import ReactDom from 'react-dom';
import { func } from 'prop-types';
import components from '@fusion/components';
import { FlowProvider, FlowClient } from '@bytorsten/react';
import { stateKey } from '@fusion/meta';

export const container = document.createElement('div');

export const Components = ({ context }) => components.map(({ identifier, component: Component }) => {
  const props = context.__props[identifier];
  const component = <Component {...props} />
  const node = document.getElementById(`container-${identifier}`);

  while(node.firstChild) {
    node.removeChild(node.firstChild);
  }

  return ReactDom.createPortal(component, node);
});

export const App = ({ children }) => {

  const client = new FlowClient();
  client.hydrate(window[stateKey]);

  return (
    <FlowProvider client={client}>
      {children({
        components: (
          <Components context={client.context} />
        )
      })}
    </FlowProvider>
  );
};

App.propTypes = {
  children: func
};

App.defaultProps = {
  children: ({ components }) => components
};
