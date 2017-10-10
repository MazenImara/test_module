<?php

namespace Drupal\test\Controller;

use Drupal\Core\Controller\ControllerBase;

class testController extends ControllerBase {

	/**
	 * Display the markup.
	 *
	 * @return array
	 */
	public function main() {
		return array(
			'#theme'   => 'main',
			'#content' => self::reset(),
		);
	}
	static public function reset() {
		$nodes  = [];
		$result = \Drupal::database()->select('node_counter', 'q')
		                             ->fields('q', ['nid', 'totalcount', 'daycount', 'timestamp', 'weeks'])
		                             ->execute();
		while ($row = $result->fetchAssoc()) {
			array_push($nodes, [
					'nid'        => $row['nid'],
					'totalcount' => $row['totalcount'],
					'daycount'   => $row['daycount'],
					'timestamp'  => $row['timestamp'],
					'weeks'      => $row['weeks'],
				]);
		}
		foreach ($nodes as $node) {
			if ($node['weeks']) {
				$days = explode(':', $node['weeks']);
				unset($days[count($days)-1]);
				if (count($days) < 5) {
					$node['weeks'] = $node['weeks'].$node['daycount'].':';
				} else {
					for ($i = 0; $i < count($days); $i++) {
						$days[$i] = $days[$i+1];
					}
					$days[count($days)-1] = $node['daycount'];
					$node['weeks']        = '';
					foreach ($days as $day) {
						$node['weeks'] = $node['weeks'].$day.':';
					}
				}
				$days = explode(':', $node['weeks']);
				unset($days[count($days)-1]);
				$node['totalcount'] = 0;
				foreach ($days as $day) {
					$node['totalcount'] = $node['totalcount']+(int) $day;
				}
				$node['daycount'] = 0;
				self::save($node);
			} else {
				$node['weeks']      = $node['daycount'].':';
				$node['totalcount'] = $node['daycount'];
				$node['daycount']   = 0;
				self::save($node);
			}
		}
		return $days;
	}

	static public function save($node) {
		\Drupal::database()->update('node_counter')
		                   ->condition('nid', $node['nid'])
		                   ->fields([
				'totalcount' => $node['totalcount'],
				'daycount'   => $node['daycount'],
				'timestamp'  => $node['timestamp'],
				'weeks'      => $node['weeks'],
			])
			->execute();
	}
}