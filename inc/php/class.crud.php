<?php
/********************************************************************************
*  Copyright notice
*
*  (c) 2014 Christoph Taubmann (info@cms-kit.org)
*  All rights reserved
*
*  This script is part of cms-kit Framework. 
*  This is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License Version 3 as published by
*  the Free Software Foundation, or (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/licenses/gpl.html
*  A copy is found in the textfile GPL.txt and important notices to other licenses
*  can be found found in LICENSES.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
************************************************************************************/

/** 
* this is the main CRUD(A)-Interface
* some comments atm in german - sorry
* 
* this class will be extended by backend/templates/TEMPLATE_NAME/crud.php
* 
* @package crud
*/

class crud
{
	public $projectName;
	public $projectPath;
	public $objectName;
	public $objectLowerName;
	public $objectId;
	public $objectFields = array('id');//$objectFieldNames
	public $objectIdName;
	public $objects;
	public $mobile = 0;
	
	public $dbi = 0;// $this->dbi
	

	public $referenceName = false;
	public $referenceLowerName;
	public $referenceId;
	public $referenceFields = array('id');//referenceFieldNames
	

	public $limit;
	public $offset;
	public $sortBy = array();
	
	public $lang;
	public $LL = array();
	
	// public Interfaces to adapt/manipulate Content-Access
	public $disallow = array();// hide Buttons
	public $getListFilter = array();// manipulate main List View/Access
	public $getContentFilter = array();// manipulate main Content View/Access (get,save,delete)
	public $getDeleteFilter = array();
	public $getSaveFilter = array();
	public $getAssocListFilter = array();// manipulate referenced List View/Access
	public $disableConnectingFor = array();
	public $inject = array();// inject additional/adapted Content
	
	public $inspect = array();// not used atm
	
	// dummy-function
	//public function logout(){}
	
	/**
	* 
	* 
	* @return 
	*/
	public function sanitize($str)
	{
		return preg_replace('/[^a-z0-9_]/si', '', $str);
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function none()
	{
		return '';
	}
	
	/**
	* Language
	* 
	* @return 
	*/
	public function L($str)
	{
		$str = trim($str);
		return (isset($this->LL[$str])) ? $this->LL[$str] : str_replace('_', ' ', $str);
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function buildFilterSelect ($objectName)
	{
		$str  = '';
		if (isset($_SESSION[$this->projectName]['filter'][$objectName]))
		{
			$str .= '<select class="filterSelect"><option value="">'.$this->L('Filter').'</option>';
			foreach ($_SESSION[$this->projectName]['filter'][$objectName] as $k => $v)
			{
				$str .= '<option';
				
				if (!empty($_GET['filterKey']) && $_GET['filterKey'] == $k)
				{
					$str .= ' selected="selected"';
				}
				if (is_array($v['data']))
				{
					foreach ($v['data'] as $dk => $dv)
					{
						$str .= ' data-'.$dk.'="'.$dv.'"';
					}
				}
				$str .= ' value="'.$k.'" title="'
						. (	is_array($v['description']) 
							? (	isset($v['description'][$this->lang])
								? $v['description'][$this->lang]
								: $v['description']['en']
							  ) 
							: $v['description']
						  )
						. '">'
						. (	is_array($v['name']) 
							? (	isset($v['name'][$this->lang])
								? $v['name'][$this->lang]
								: $v['name']['en']
							  )
							: $v['name']
						  )
						. '</option>';
			}
			$str .= '</select>';
		}
		return $str;
	}
	
	public function prepareFilterArray ($arr)
	{
		$callback = function (&$value)
		{
			// replace custom Placeholders with dynamically generated values/objects
			$value = preg_replace(
						array(// Needles
							'/###TIMESTAMP###/'
						),
						array(// Replacements
							time()
						),
						$value
					);
		};
		
		array_walk_recursive($arr, $callback);
		return $arr;
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function getList ()
	{
		
		// if we have a filter-key we overwrite all rules
		if ( !empty($_GET['filterKey']) && isset($_SESSION[$this->projectName]['filter'][$this->objectName][$_GET['filterKey']]) )
		{
			$f = $_SESSION[$this->projectName]['filter'][$this->objectName][$_GET['filterKey']];
			
			if (!empty($f['select'])) $this->getListFilter = $this->prepareFilterArray($f['select']);
			if (!empty($f['sort'])) $this->sortBy = $f['sort'];
			if (!empty($f['show'])) $this->objectFields = $f['show'];
		}// filter END
		
		
		
		// we have to look for the list-part where our active element is located
		if ($this->objectId)
		{
			$this->offset = 0;
			while($s = $this->getListString(false))
			{
				if(in_array($this->objectId, $this->idsInList))
				{
					return $this->offset;
				}
				$this->offset += $this->limit;
			}
		}
		else
		{
			return $this->getListString(false);
		}
	}
	
	/**
	* 
	* ???????????????
	* @return 
	*/
	public function createButtonHtml($ico, $lbl=false, $action=false, $enabled=true)
	{
		 return '<button'.($enabled?'':' disabled="disabled"').' rel="'.$ico.'"'.($action?' onclick="'.$action.'"':'').'>'.($lbl?$this->L($lbl):'.').'</button>';
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function getTreePath()
	{
		$ttype = $_GET['tType'];
		$dbn = $this->projectName . '\\DB';
        $db = new $dbn();
		//echo $db;
		switch ($_GET['tType'])
		{
			case 'Tree':
				$prep = $db->instance($this->dbi)->prepare('SELECT GROUP_CONCAT(p.id) AS il FROM `'.$this->objectName.'` n, `'.$this->objectName.'` p WHERE n.treeleft BETWEEN p.treeleft AND p.treeright AND n.id = ? ORDER BY n.treeleft;');
				$prep->execute	(	array(
										$this->objectId
										)
								);
				return $prep->fetch()->il;
			break;
			case 'Graph':
				$prep = $db->instance($this->dbi)->prepare('SELECT GROUP_CONCAT(`pid`) AS il FROM `'.$this->objectName.'matrix` WHERE `id`= ?;');
				$prep->execute	(	array(
										$this->objectId
										)
								);
				return $prep->fetch()->il;
			break;
		}
		
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function arrayToObject($d)
	{
		if (is_array($d))
		{
			return (object) array_map(__METHOD__, $d);
		}
		else
		{
			return $d;
		}
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function objectToArray($data)
	{
		if (is_array($data) || is_object($data))
		{
			$result = array();
			foreach ($data as $key => $value)
			{
				$result[$key] = $this->objectToArray($value);
			}
			return $result;
		}
		return $data;
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function getElementBy($id, $filter)
	{
		array_push ($filter, array('id', '=', $id));
		
		$o = $this->projectName.'\\'.$this->objectName;
		$obj = new $o();
		
		$list = $obj->GetList($filter, array(), 1);
		if (!isset($list[0])) exit($this->L('no_element_found'));
		return $list[0];
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function saveContent()
	{
		// if id is 0 save as new entry
		if ($this->objectId == 0)
		{
			$o = $this->projectName.'\\'.$this->objectName;
			$item = new $o();
		}
		else
		{
			$item = $this->getElementBy($this->objectId, $this->getSaveFilter);
		}
		
		foreach ($_POST as $k => $v)
		{
			$item->$k = $v;
		}
		
		foreach ($this->inject as $k => $v)
		{
			$item->$k = $v;
		}
		
		
		if ($id = $item->Save())
		{
			return $id;
			/*return	(
						($this->objectId == $id)
						? $this->L('saved') // we saved the same content: return a message
						: $id // we saved a new entry: return the new ID
					);*/
		}
		else
		{
			return '[[' . $this->L('error') . ']]';
		}
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function createContent()
	{
		$o = $this->projectName.'\\'.$this->objectName;
		$obj = new $o();
		
		foreach ($this->inject as $k => $v)
		{
			$obj->{$k} = $v;
		}
		
		if ($id = $obj->Save())
		{
			return $id; // return ID as "Success-Message"
		}
		else
		{
			return '[[' . $this->L('error') . ']]';
		}
	}
	
	/**
	* 
	* 
	* @return 
 
	*/
	public function deleteContent()
	{
		//$obj = new $this->objectName();
		
		$item = $this->getElementBy($this->objectId, $this->getDeleteFilter);//$obj->Get($this->objectId);
		
		if ($item->Delete(false))
		{
			return $this->L('deleted');
		}
		else
		{
			return '[[' . $this->L('error') . ']]';
		}
	}
	
	
	/**
	* 
	* 
	* @return 
	*/
	public function multiValue()
	{
		if ($_GET['input']) {
			$o = $this->projectName.'\\'.$this->objectName;
			$obj = new $o();
			$ids = explode(',', $this->objectId);
			$cnt = 0;
			foreach ($ids as $id)
			{
				if ($el = $obj->Get($id))
				{
					$el->{$_GET['input']} = $_POST['val'];
					$el->Save();
					$cnt++;
				}
			}
			return $cnt.' '.$this->L('saved');
		}
		else
		{
			return '';
		}
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function multiDelete()
	{
		$o = $this->projectName.'\\'.$this->objectName;
		$obj = new $o();
		$ids = explode(',', $this->objectId);
		
		$cnt = 0;
		foreach ($ids as $id)
		{
			//if($el = $obj->Get($id))
			if ($el = $this->getElementBy($id))
			{
				if ($el->Delete()) $cnt++;
			}
		}
		return '<h2>'.$cnt.' '.$this->L('deleted').'</h2><a href="backend.php?project='.$this->projectName.'&object='.$this->objectName.'">'.$this->L('reload').'</a>';
	}
	
	
	/**
	* 
	* 
	* @return bool
	*/
	public function addReference()
	{
		// cancel if Relation dosent exist
		if (!isset($this->objects[$this->objectName]['rel'][$this->referenceName])) 
		{
			exit('[[' . $this->L('error') . ' ' . $this->L('relation_dosent_exist'). ']]');
		}
		
		//require_once($this->ppath.'/objects/class.'.$this->referenceName.'.php');
		
		$o = $this->projectName.'\\'.$this->objectName;
		$obj = new $o();
		$item = $obj->Get($this->objectId);
		$r = $this->projectName.'\\'.$this->referenceName;
		$ref = new $r();
		$refitem = $ref->Get($this->referenceId);
		
		$action = ($this->objects[$this->objectName]['rel'][$this->referenceName] == 'p') ? 'Set'.$this->referenceName : 'Add'.$this->referenceName;
		
		$item->$action($refitem);
		return $item->Save();
	}
	
	/**
	* 
	* 
	* @return 
	*/
	public function saveReferences()
	{
		
		// cancel if Relation dosent exist
		if (!isset($this->objects[$this->objectName]['rel'][$this->referenceName])) 
		{
			exit('[[' . $this->L('error') . ' ' . $this->L('relation_dosent_exist'). ']]');
		}
		
		// References => array( shown, next, offset )
		$a = $_SESSION[$this->projectName]['_'];
		
		// parse ID-String => $l
		parse_str ($_POST['order']);
		
		// if the posted List is empty
		if (!isset($l))
		{
			$l = array();
		}
		
		
		$toAdd 	= array_diff($l, $a[0]);
		$toDel 	= array_diff($a[0], $l);
		
		// Sort-Counter == old Offset+1
		$s = $a[2] + 1;
		$dbn = $this->projectName.'\\DB';
        $db = new $dbn();
		
		switch( $this->objects[$this->objectName]['rel'][$this->referenceName] )
		{
			
			// Sibling-List -----------------------------------------------------------------
			case 's':
				
				// get the Name of the Map-Table
				$m = array($this->objectName, $this->referenceName);
				natcasesort($m);
				$m = implode('', $m);
				
				// delete Relations if any
				if (count($toDel) > 0)
				{
					$query0 = 'DELETE FROM `'.$m.'map` WHERE `'.$this->referenceName.'id` = ? AND `'.$this->objectName.'id` = ?;';
					$prepare0 = $db->instance($this->dbi)->prepare($query0);
					
					foreach ($toDel as $i)
					{
						try
						{
							$prepare0->execute ( array( $i, $this->objectId ) );
						}
						catch (PDOException $e)
						{
							exit('[[Error deleting Relations: ' . $e->getMessage() . ']]');
						}
					}
				}
				
				// add Relations if any
				if (count($toAdd)>0)
				{
					$query1 = 'INSERT INTO `'.$m.'map` (`'.$this->referenceName.'id`, `'.$this->objectName.'id`, `'.$this->referenceName.'sort`, `'.$this->objectName.'sort`) VALUES (?, ?, 0, 0);';
					
					$prepare1 = $db->instance($this->dbi)->prepare($query1);
					foreach ($toAdd as $i)
					{
						try
						{
							$prepare1->execute ( array( $i, $this->objectId ) );
						}
						catch (PDOException $e)
						{
							exit('[[Error adding Relations: ' . $e->getMessage() . ']]');
						}
					}
				}
				
				
				// update Sorting
				
				$query2 = 'UPDATE `'.$m.'map` SET `'.$this->referenceName.'sort` = ? WHERE `'.$this->referenceName.'id` = ? AND `'.$this->objectName.'id` = ?;';
				$prepare2 = $db->instance($this->dbi)->prepare($query2);
				
				// 1. update all Relations currently shown in List
				foreach ($l as $i)
				{
					try
					{
						$prepare2->execute ( array( $s, $i, $this->objectId ) );
					}
					catch (PDOException $e)
					{
						exit('[[Error updating Relations:' . $e->getMessage() . ']]');
					}
					$s++;
				}
				// 2. update further Relations if any
				if (count($toAdd)>0 || count($toDel)>0)
				{
					foreach ($a[1] as $i)
					{
						try
						{
							$prepare2->execute ( array( $s, $i, $this->objectId ) );
						}
						catch (PDOException $e)
						{
							exit('[[Error updating Relations:' . $e->getMessage() . ']]');
						}
						$s++;
					}
				}
				
			break;
			
			// Child-List ---------------------------------------------------------------------
			case 'c':
				
				
				// delete Relations if any
				if (count($toDel)>0)
				{
					$query1 = 'UPDATE `'.$this->referenceName.'` SET `'.$this->objectName.'id`=\'0\' WHERE `id` = ?;';
					$prepare1 = $db->instance($this->dbi)->prepare($query1);
					foreach($toDel as $i)
					{
						try
						{
							$prepare1->execute( array( $i ) );
						}
						catch (PDOException $e)
						{
							exit('[[Error updating Relations: ' . $e->getMessage(). ']]');
						}
					}
				}
				
				// add Relations if any
				if (count($toAdd)>0)
				{
					$query2 = 'UPDATE `'.$this->referenceName.'` SET `'.$this->objectName.'id` = ? WHERE `id` = ?;';
					$prepare2 = $db->instance($this->dbi)->prepare($query2);
					foreach($toAdd as $i)
					{
						try
						{
							$prepare2->execute ( array( $this->objectId, $i ) );
						}
						catch (PDOException $e)
						{
							exit('[[Error updating Relations: ' . $e->getMessage(). ']]');
						}
					}
				}
				
				// if there is any Sort-Field defined
				if ($this->objects[$this->referenceName]['rel'][$this->objectName.'sort'])
				{
					
					$query3 = 'UPDATE `'.$this->referenceName.'` SET `'.$this->objectName.'sort` = ? WHERE `id` = ?;';
					$prepare3 = $db->instance($this->dbi)->prepare($query3);
					
					// 1. update all Relations currently shown in List
					foreach ($l as $i)
					{
						try
						{
							$prepare3->execute ( array( $s, $i ) );
						}
						catch (PDOException $e)
						{
							exit('[[Error updating Relations: ' . $e->getMessage(). ']]');
						}
						$s++;
					}
					// 2. update further Relations if any
					if (count($toAdd)>0 || count($toDel)>0)
					{
						foreach ($a[1] as $i)
						{
							try
							{
								$prepare2->execute ( array( $s, $i ) );
							}
							catch (PDOException $e)
							{
								exit('[[Error updating Relations: ' . $e->getMessage() . ']]');
							}
							$s++;
						}
					}
				}
				
			break;
			
			// Parent-Element -----------------------------------------------------------------
			case 'p':
				$query = 'UPDATE `'.$this->objectName.'` SET '.$this->referenceName.'id = ? WHERE `id` = ?;';
				$prepare = $db->instance($this->dbi)->prepare($query);
				
				try
				{
					$prepare->execute ( array( $l[0], $this->objectId ) );
				}
				catch (PDOException $e)
				{
					exit('[[Error updating Relations: ' . $e->getMessage(). ']]');
				}
			break;
			
			
		} // switch END
		
		
		return $this->L('connections_saved');
	
	}
}
