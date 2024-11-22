<?php

namespace App\Http\Algorithm;

class Dijkstra
{
    private $graph;
    private $distances;
    private $previous;
    private $queue;

    public function __construct($graph)
    {
        $this->graph = $graph;
        $this->distances = [];
        $this->previous = [];
        $this->queue = new \SplPriorityQueue();
    }

    public function shortestPath($start, $end)
    {
        foreach ($this->graph as $vertex => $edges) {
            $this->distances[$vertex] = INF;
            $this->previous[$vertex] = null;
            $this->queue->insert($vertex, INF);
        }

        $this->distances[$start] = 0;
        $this->queue->insert($start, 0);

        while (!$this->queue->isEmpty()) {
            $u = $this->queue->extract();

            if ($u === $end) {
                break;
            }

            if (!empty($this->graph[$u])) {
                foreach ($this->graph[$u] as $neighbor => $cost) {
                    $alt = $this->distances[$u] + $cost;
                    if ($alt < $this->distances[$neighbor]) {
                        $this->distances[$neighbor] = $alt;
                        $this->previous[$neighbor] = $u;
                        $this->queue->insert($neighbor, $alt);
                    }
                }
            }
        }

        $path = [];
        $u = $end;
        while (isset($this->previous[$u])) {
            array_unshift($path, $u);
            $u = $this->previous[$u];
        }

        if ($this->distances[$end] !== INF) {
            array_unshift($path, $start);
        }

        return $path;
    }
}
