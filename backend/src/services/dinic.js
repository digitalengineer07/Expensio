class Edge {
  constructor(to, reverseIndex, capacity, meta = null) {
    this.to = to;
    this.reverseIndex = reverseIndex;
    this.capacity = capacity;
    this.meta = meta;
  }
}

class Dinic {
  constructor(size) {
    this.size = size;
    this.graph = Array.from({ length: size }, () => []);
    this.level = new Array(size).fill(-1);
    this.iter = new Array(size).fill(0);
  }

  addEdge(from, to, capacity, meta = null) {
    const forward = new Edge(to, this.graph[to].length, capacity, meta);
    const backward = new Edge(from, this.graph[from].length, 0n, null);
    this.graph[from].push(forward);
    this.graph[to].push(backward);
    return forward;
  }

  bfs(source, sink) {
    this.level.fill(-1);
    const queue = [source];
    this.level[source] = 0;

    for (let head = 0; head < queue.length; head += 1) {
      const node = queue[head];

      for (const edge of this.graph[node]) {
        if (edge.capacity > 0n && this.level[edge.to] < 0) {
          this.level[edge.to] = this.level[node] + 1;
          queue.push(edge.to);
        }
      }
    }

    return this.level[sink] >= 0;
  }

  dfs(node, sink, flow) {
    if (node === sink) {
      return flow;
    }

    for (; this.iter[node] < this.graph[node].length; this.iter[node] += 1) {
      const edge = this.graph[node][this.iter[node]];

      if (edge.capacity === 0n || this.level[node] >= this.level[edge.to]) {
        continue;
      }

      const nextFlow = this.dfs(
        edge.to,
        sink,
        flow < edge.capacity ? flow : edge.capacity
      );

      if (nextFlow > 0n) {
        edge.capacity -= nextFlow;
        this.graph[edge.to][edge.reverseIndex].capacity += nextFlow;
        return nextFlow;
      }
    }

    return 0n;
  }

  maxFlow(source, sink) {
    let flow = 0n;
    const infinite = 10n ** 30n;

    while (this.bfs(source, sink)) {
      this.iter.fill(0);

      let pushed = this.dfs(source, sink, infinite);
      while (pushed > 0n) {
        flow += pushed;
        pushed = this.dfs(source, sink, infinite);
      }
    }

    return flow;
  }
}

module.exports = Dinic;
