import React from 'react';
import ReactDom from 'react-dom';
import { FlowProvider, FlowClient } from '@bytorsten/react';

import Component from '@fusion/component';
import { stateKey, containerId } from '@fusion/meta';

const client = new FlowClient();
client.hydrate(window[stateKey]);

ReactDom.hydrate((
  <FlowProvider client={client}>
    <Component {...client.context} />
  </FlowProvider>
), document.getElementById(containerId));
