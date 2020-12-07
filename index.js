///<reference path="types.d.ts" />;
const procURL = URL.createObjectURL(
  new Blob(
    [
      `const frame = 36;
const chunk = 1024;

class PlaybackProcessor extends AudioWorkletProcessor {
constructor() {
  super();
  this.buffers = [];
  this.started = false;
  this.port.postMessage("initialized");
  this.port.onmessage = this.handleMesg.bind(this);
  this.rptr = 0;
  this.loss = 0;
  this.total = 0;
  this.rms = 0;
}
handleMesg(evt) {
  this.readable = evt.data.readable;
  let reader = this.readable.getReader();
  let that = this;
  reader.read().then(function process({ done, value }) {
    if (done) {
      that.port.postMessage({ done: 1 });
      return;
    }
    let offset = 0;
    value &&
      that.port.postMessage({stats:
        {
          rms: that.rms,
          buffered: (that.buffers.length / 350).toFixed(3),
          lossPercent: ((that.loss / that.total) * 100).toFixed(2)
        }
      });
    while (value.length >= chunk) {
      const b = value.slice(0, chunk);
      that.buffers.push(b);
      value = value.slice(chunk);
    }
    if (that.started === false && that.buffers.length > 10) {
      that.port.postMessage({ ready: 1 });
      that.started = true;
    }
    reader.read().then(process);
  });
}

process(inputs, outputs, parameters) {
  if (this.started === false) return true;

  if (this.buffers.length === 0) {
    this.loss++;
    return true;
  }
  this.total++;

  const ob = this.buffers.shift();
  const fl = new Float32Array(ob.buffer);
  let sum = 0;
  for (let i = 0; i < 128; i++) {
    for (let ch = 0; ch < 2; ch++) {
      outputs[0][ch][i] = fl[i * 2 + ch];
      sum += fl[i * 2 + ch] * fl[i * 2 + ch];
    }
  }
  this.rms = Math.sqrt(sum / 256);
  return true;
}
}
registerProcessor("playback-processor", PlaybackProcessor);
`,
    ],
    { type: "application/javascript" }
  )
);

let node, worker, ctx;
const defaultConfig = { sampleRate: 48000, nchannels: 2, bitdepth: 32 };
export const initProcessor = async (config = defaultConfig) => {
  ctx = new AudioContext({
    sampleRate: config.sampleRate || 44100,
    latencyHint: "playback",
  });
  await ctx.audioWorklet.addModule(procURL);
  node = new AudioWorkletNode(ctx, "playback-processor", {
    outputChannelCount: [2],
  });
  node.connect(ctx.destination);
  return node;
};
export const postStream = async (readable) => {
  if (!node) node = await initProcessor();
  if (!node) throw new Error("unable to start proc");
  node.port.postMessage({ readable });
  if (ctx.state === "suspended") {
    await ctx.resume();
  }
};
export const setup = async (config) => {
  config = config ||= {
    nchannels: 2,
    sampleRate: 44100,
    bitdepth: 32,
  };
  node = await initProcessor(config);
  worker = new Worker("./build/playback-worker.js", { type: "module" });
  worker.postMessage({ port: node.port }, [node.port]);
  await new Promise((resolve) => {
    worker.onmessage = async ({ data: { ready } }) => {
      if (ready === 1) {
        await ctx.resume();
        resolve();
      }
    };
  });
  function queue(url) {
    worker.postMessage({ url });
  }
  function playNow(url) {
    worker.postMessage({ url });
  }
  function next() {
    worker.postMessage({ cmd: "ff" });
  }
  return {
    worker,
    node,
    queue,
    playNow,
    next,
  };
};
