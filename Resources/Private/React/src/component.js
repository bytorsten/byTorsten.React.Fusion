import React from 'react';
import ReactDom from 'react-dom';
import { FlowProvider, FlowClient } from '@bytorsten/react';

import Component from '@fusion/component';
import { stateKey, containerId } from '@fusion/meta';

if (!window['__FLOW_HELPER__']) {
  console.error('%cCould not find internal data in dom, did you but "byTorsten.React:Script" somewhere above this component?', 'background: red; color: white; padding: 2px');
} else {
  const client = new FlowClient();
  client.hydrate(window[stateKey]);

  ReactDom.hydrate((
    <FlowProvider client={client}>
      <Component {...client.context} />
    </FlowProvider>
  ), document.getElementById(containerId));
}
