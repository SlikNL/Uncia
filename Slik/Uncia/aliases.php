<?php
function confirm()
{
	return call_user_func_array('\\Slik\\Uncia\\confirm', func_get_args());
}

function Out()
{
	return call_user_func_array('\\Slik\\Uncia\\Out', func_get_args());
}

function preg()
{
	return call_user_func_array('\\Slik\\Uncia\\preg', func_get_args());
}

function prompt()
{
	return call_user_func_array('\\Slik\\Uncia\\prompt', func_get_args());
}

function run()
{
	return call_user_func_array('\\Slik\\Uncia\\run', func_get_args());
}

function stderr()
{
	return call_user_func_array('\\Slik\\Uncia\\stderr', func_get_args());
}

function stdin()
{
	return call_user_func_array('\\Slik\\Uncia\\stdin', func_get_args());
}

function stdout()
{
	return call_user_func_array('\\Slik\\Uncia\\stdout', func_get_args());
}

function str()
{
	return call_user_func_array('\\Slik\\Uncia\\str', func_get_args());
}

function ttycolor()
{
	return call_user_func_array('\\Slik\\Uncia\\ttycolor', func_get_args());
}
